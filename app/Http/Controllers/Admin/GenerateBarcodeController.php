<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarcodeRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\BarcodeGeneratorService;
use App\Services\QrGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class GenerateBarcodeController extends Controller
{
    public function __construct(
        private BarcodeGeneratorService $generator,
        private QrGeneratorService $qrService,
        private ActivityLogService $activityLog,
    ) {}

    /**
     * Show the generate-barcode list page.
     */
    public function index(): View
    {
        $filterLimit = max((int) config('sto.admin_filter_options_limit', 500), 1);

        return view('admin.generate-barcode', [
            'plants'    => Plant::active()->orderBy('name')->limit($filterLimit)->get(),
            'locations' => Location::active()->orderBy('name')->limit($filterLimit)->get(),
            'users'     => User::active()->orderBy('name')->limit($filterLimit)->get(),
            'materials' => MasterMaterial::active()->orderBy('material_code')->limit($filterLimit)->get(),
        ]);
    }

    /**
     * DataTable server-side endpoint.
     */
    public function datatable(Request $request): JsonResponse
    {
        $query = BarcodeRequest::with(['user', 'plant', 'location', 'reviewedBy'])
            ->select('barcode_requests.*');

        // --- Filters ---
        $status = $request->input('filter_status');
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        } else {
            // Default: show pending first
            $query->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')");
        }

        if ($plantId = $request->input('filter_plant')) {
            $query->where('plant_id', $plantId);
        }

        if ($locationId = $request->input('filter_location')) {
            $query->where('location_id', $locationId);
        }

        if ($requesterId = $request->input('filter_requester')) {
            $query->where('user_id', $requesterId);
        }

        if ($materialCode = $request->input('filter_material')) {
            $query->where('material_code', $materialCode);
        }

        $search = $request->input('search.value');
        if ($search) {
            $rawSearch = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function (Builder $q) use ($rawSearch) {
                $q->where('material_code', 'like', "%{$rawSearch}%")
                    ->orWhere('material_name', 'like', "%{$rawSearch}%")
                    ->orWhere('lot_number', 'like', "%{$rawSearch}%")
                    ->orWhere('shape_name', 'like', "%{$rawSearch}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$rawSearch}%"))
                    ->orWhereHas('plant', fn ($p) => $p->where('name', 'like', "%{$rawSearch}%"))
                    ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$rawSearch}%"));
            });
        }

        $maxLength = max((int) config('sto.datatable_max_length', 100), 1);
        $start  = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), $maxLength);

        $total    = (clone $query)->count();
        $records  = $query->orderByDesc('created_at')->skip($start)->take($length)->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $records->map(function (BarcodeRequest $r, int $idx) use ($total, $start) {
                return [
                    'id'               => $r->id,
                    'no'               => $total - $start - $idx,
                    'material_code'    => $r->material_code,
                    'material_name'    => $r->material_name,
                    'shape_name'       => $r->shape_name,
                    'size'             => $r->size,
                    'lot_number'       => $r->lot_number,
                    'qty'              => $r->qty,
                    'plant'            => $r->plant?->name ?? '-',
                    'location'         => $r->location?->name ?? '-',
                    'requester'        => $r->user?->name ?? '-',
                    'status'           => $r->status,
                    'reviewed_by'      => $r->reviewedBy?->name ?? '-',
                    'reviewed_at'      => $r->reviewed_at?->format('d-m-Y H:i') ?? '-',
                    'generated_barcode_material' => $r->generated_barcode_material,
                    'created_at'       => $r->created_at?->format('d-m-Y H:i') ?? '-',
                ];
            })->values(),
        ]);
    }

    /**
     * Validate (preview) a barcode request without saving anything.
     * Read-only — must NOT write to the database.
     */
    public function validateData(BarcodeRequest $barcodeRequest): JsonResponse
    {
        abort_if($barcodeRequest->status !== 'pending', 422, 'Request ini sudah diproses.');

        $result = $this->generator->build([
            'shape_code'    => $barcodeRequest->shape_code,
            'material_code' => $barcodeRequest->material_code,
            'thickness'     => $barcodeRequest->thickness,
            'width'         => $barcodeRequest->width,
            'diameter'      => $barcodeRequest->diameter,
            'length'        => $barcodeRequest->length,
        ]);

        $material = MasterMaterial::findByCode($barcodeRequest->material_code);

        $checks = [
            [
                'label'  => 'Material ditemukan di Master',
                'ok'     => $material !== null,
                'detail' => $material ? "{$material->material_code} — {$material->material_name}" : 'Tidak ditemukan / tidak aktif',
            ],
            [
                'label'  => 'Dimensi valid',
                'ok'     => $result['valid'],
                'detail' => $result['valid'] ? 'Semua dimensi dalam range yang valid' : implode(', ', $result['errors']),
            ],
            [
                'label'  => 'Lot Number diisi',
                'ok'     => filled($barcodeRequest->lot_number),
                'detail' => $barcodeRequest->lot_number ?: '(kosong)',
            ],
            [
                'label'  => 'Lokasi valid',
                'ok'     => $barcodeRequest->location_id !== null,
                'detail' => $barcodeRequest->location?->name ?? '(tidak ada)',
            ],
        ];

        return response()->json([
            'success'          => $result['valid'],
            'barcode_material' => $result['barcode_material'],
            'checks'           => $checks,
            'errors'           => $result['errors'],
            'request_data'     => [
                'material_code' => $barcodeRequest->material_code,
                'material_name' => $barcodeRequest->material_name,
                'shape_name'    => $barcodeRequest->shape_name,
                'size'          => $barcodeRequest->size,
                'lot_number'    => $barcodeRequest->lot_number,
                'plant'         => $barcodeRequest->plant?->name,
                'location'      => $barcodeRequest->location?->name,
            ],
        ]);
    }

    /**
     * Generate the barcode for a request.
     * Validates server-side, saves barcode_material, qty, status, reviewed_by, reviewed_at.
     */
    public function generate(Request $request, BarcodeRequest $barcodeRequest): JsonResponse
    {
        if ($barcodeRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request ini sudah diproses (status: ' . $barcodeRequest->status . ').',
            ], 422);
        }

        $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:99999'],
        ]);
        $validated['qty'] = 1;

        // Server-side re-validate
        $result = $this->generator->build([
            'shape_code'    => $barcodeRequest->shape_code,
            'material_code' => $barcodeRequest->material_code,
            'thickness'     => $barcodeRequest->thickness,
            'width'         => $barcodeRequest->width,
            'diameter'      => $barcodeRequest->diameter,
            'length'        => $barcodeRequest->length,
        ]);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi barcode gagal: ' . implode('; ', $result['errors']),
                'errors'  => $result['errors'],
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldValues = $barcodeRequest->only(['status', 'generated_barcode_material', 'qty', 'reviewed_by_user_id', 'reviewed_at']);

            $barcodeRequest->update([
                'status'                   => 'approved',
                'generated_barcode_material' => $result['barcode_material'],
                'qty'                      => $validated['qty'],
                'reviewed_by_user_id'      => $request->user()->id,
                'reviewed_at'              => now(),
            ]);

            $this->activityLog->record(
                $request->user(),
                'barcode_request.generated',
                $barcodeRequest,
                oldValues: $oldValues,
                newValues: [
                    'status'                   => 'approved',
                    'generated_barcode_material' => $result['barcode_material'],
                    'qty'                      => $validated['qty'],
                    'reviewed_at'              => now()->format('Y-m-d H:i:s'),
                ],
            );

            DB::commit();
            Cache::forget('scan_overview:*');
            Cache::forget('sidebar_badges');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('GenerateBarcodeController@generate failed', [
                'barcode_request_id' => $barcodeRequest->id,
                'exception'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data. Silakan coba lagi.',
            ], 500);
        }

        $fullBarcode = $barcodeRequest->fresh()->full_barcode;

        return response()->json([
            'success'          => true,
            'message'          => 'Barcode berhasil di-generate.',
            'barcode_material' => $result['barcode_material'],
            'full_barcode'     => $fullBarcode,
            'label_url'        => route('admin.generate-barcode.label', $barcodeRequest->id),
        ], 201);
    }

    /**
     * Reject a barcode request. Requires rejection_reason.
     */
    public function reject(Request $request, BarcodeRequest $barcodeRequest): JsonResponse
    {
        if ($barcodeRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request ini sudah diproses (status: ' . $barcodeRequest->status . ').',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $barcodeRequest->only(['status', 'rejection_reason']);

            $barcodeRequest->update([
                'status'             => 'rejected',
                'rejection_reason'   => $validated['rejection_reason'],
                'reviewed_by_user_id' => $request->user()->id,
                'reviewed_at'        => now(),
            ]);

            $this->activityLog->record(
                $request->user(),
                'barcode_request.rejected',
                $barcodeRequest,
                oldValues: $oldValues,
                newValues: [
                    'status'           => 'rejected',
                    'rejection_reason' => $validated['rejection_reason'],
                    'reviewed_at'      => now()->format('Y-m-d H:i:s'),
                ],
            );

            DB::commit();
            Cache::forget('scan_overview:*');
            Cache::forget('sidebar_badges');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('GenerateBarcodeController@reject failed', [
                'barcode_request_id' => $barcodeRequest->id,
                'exception'          => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan penolakan. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil ditolak.',
        ]);
    }

    /**
     * Print a single QR label PDF for an approved barcode request.
     */
    public function label(BarcodeRequest $barcodeRequest): Response
    {
        abort_unless(
            $barcodeRequest->status === 'approved' && $barcodeRequest->generated_barcode_material,
            404,
            'Label hanya tersedia untuk request yang sudah di-approve.'
        );

        $fullBarcode = $barcodeRequest->full_barcode;
        $qrDataUri   = $this->qrService->generateDataUri($fullBarcode, 280);

        $pdf = Pdf::loadView('admin.generate-barcode-label', [
            'request'   => $barcodeRequest,
            'qrDataUri' => $qrDataUri,
            'company'   => config('sto.company_name'),
        ]);

        // Label size: 10cm × 4.2cm (landscape)
        $pdf->setPaper([0, 0, 283.46, 119.06], 'portrait'); // ~10cm × 4.2cm in points

        return $pdf->download("label-{$barcodeRequest->material_code}-{$barcodeRequest->lot_number}.pdf");
    }

    /**
     * Print bulk QR label PDF for multiple approved barcode requests.
     */
    public function labelBulk(Request $request): Response
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'exists:barcode_requests,id'],
        ]);

        $requests = BarcodeRequest::with(['plant', 'location'])
            ->whereIn('id', $validated['ids'])
            ->where('status', 'approved')
            ->whereNotNull('generated_barcode_material')
            ->get();

        abort_if($requests->isEmpty(), 404, 'Tidak ada request yang valid untuk dicetak.');

        // Generate QR for each request
        $items = $requests->map(function (BarcodeRequest $r) {
            return [
                'request'   => $r,
                'qrDataUri' => $this->qrService->generateDataUri($r->full_barcode, 280),
            ];
        });

        $pdf = Pdf::loadView('admin.generate-barcode-label-bulk', [
            'items'   => $items,
            'company' => config('sto.company_name'),
        ]);

        $pdf->setPaper([0, 0, 283.46, 119.06], 'portrait');

        return $pdf->download("labels-bulk-" . now()->format('Ymd-His') . ".pdf");
    }
}
