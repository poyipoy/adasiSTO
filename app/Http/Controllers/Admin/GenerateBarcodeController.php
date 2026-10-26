<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarcodeRequestRequest;
use App\Models\BarcodeRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\User;
use App\Services\BarcodeGeneratorService;
use App\Services\BarcodeRequestService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GenerateBarcodeController extends Controller
{
    public function __construct(
        private BarcodeGeneratorService $generator,
        private BarcodeRequestService $barcodeRequests,
    ) {}

    public function index(): View
    {
        $filterLimit = max((int) config('sto.admin_filter_options_limit', 500), 1);

        return view('admin.generate-barcode', [
            'plants' => Plant::active()->orderBy('name')->limit($filterLimit)->get(),
            'locations' => Location::active()->orderBy('name')->limit($filterLimit)->get(),
            'users' => User::active()->orderBy('name')->limit($filterLimit)->get(),
            'materials' => MasterMaterial::active()->orderBy('material_code')->limit($filterLimit)->get(),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = BarcodeRequest::with(['user', 'plant', 'location', 'reviewedBy', 'printedBy'])
            ->select('barcode_requests.*');
        $recordsTotal = (clone $query)->count();

        if ($status = $request->input('filter_status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
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

        if ($search = $request->input('search.value')) {
            $rawSearch = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function (Builder $inner) use ($rawSearch) {
                $inner->where('material_code', 'like', "%{$rawSearch}%")
                    ->orWhere('material_name', 'like', "%{$rawSearch}%")
                    ->orWhere('lot_number', 'like', "%{$rawSearch}%")
                    ->orWhere('shape_name', 'like', "%{$rawSearch}%")
                    ->orWhereHas('user', fn ($relation) => $relation->where('name', 'like', "%{$rawSearch}%"))
                    ->orWhereHas('plant', fn ($relation) => $relation->where('name', 'like', "%{$rawSearch}%"))
                    ->orWhereHas('location', fn ($relation) => $relation->where('name', 'like', "%{$rawSearch}%"));
            });
        }

        $recordsFiltered = (clone $query)->count();
        $maxLength = max((int) config('sto.datatable_max_length', 100), 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), $maxLength);
        $rows = $query
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'printed', 'rejected')")
            ->orderByDesc('created_at')
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows->map(fn (BarcodeRequest $row, int $index) => [
                'id' => $row->id,
                'no' => $recordsFiltered - $start - $index,
                'material_code' => $row->material_code,
                'material_name' => $row->material_name,
                'shape_name' => $row->shape_name,
                'size' => $row->size,
                'lot_number' => $row->lot_number,
                'qty' => 1,
                'print_count' => $row->print_count,
                'plant' => $row->plant?->name ?? '-',
                'location' => $row->location?->name ?? '-',
                'requester' => $row->user?->name ?? '-',
                'status' => $row->status,
                'reviewed_by' => $row->reviewedBy?->name ?? '-',
                'reviewed_at' => $row->reviewed_at?->format('d-m-Y H:i') ?? '-',
                'printed_by' => $row->printedBy?->name ?? '-',
                'printed_at' => $row->printed_at?->format('d-m-Y H:i') ?? '-',
                'generated_barcode_material' => $row->generated_barcode_material,
                'created_at' => $row->created_at?->format('d-m-Y H:i') ?? '-',
            ])->values(),
        ]);
    }

    public function locations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
        ]);

        $locations = Location::active()
            ->where('plant_id', $validated['plant_id'])
            ->orderBy('name')
            ->get(['id', 'plant_id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function store(StoreBarcodeRequestRequest $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $barcodeRequest = $this->barcodeRequests->create($request->user(), $request->validated(), true);

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil ditambahkan sebagai Pending.',
            'data' => ['id' => $barcodeRequest->id],
        ], 201);
    }

    public function validateData(BarcodeRequest $barcodeRequest): JsonResponse
    {
        abort_if($barcodeRequest->status !== BarcodeRequest::STATUS_PENDING, 422, 'Request ini sudah diproses.');
        $result = $this->generator->build($barcodeRequest->only([
            'shape_code', 'material_code', 'thickness', 'width', 'diameter', 'length',
        ]));
        $material = MasterMaterial::findByCode($barcodeRequest->material_code);

        return response()->json([
            'success' => $result['valid'] && $material !== null,
            'barcode_material' => $result['barcode_material'],
            'checks' => [
                ['label' => 'Material ditemukan di Master', 'ok' => $material !== null, 'detail' => $material?->material_name ?? 'Tidak ditemukan'],
                ['label' => 'Dimensi valid', 'ok' => $result['valid'], 'detail' => $result['valid'] ? 'Valid' : implode(', ', $result['errors'])],
                ['label' => 'Lot Number diisi', 'ok' => filled($barcodeRequest->lot_number), 'detail' => $barcodeRequest->lot_number],
                ['label' => 'Lokasi valid', 'ok' => $barcodeRequest->location_id !== null, 'detail' => $barcodeRequest->location?->name ?? '-'],
            ],
            'errors' => $result['errors'],
            'request_data' => [
                'material_code' => $barcodeRequest->material_code,
                'material_name' => $barcodeRequest->material_name,
                'shape_name' => $barcodeRequest->shape_name,
                'size' => $barcodeRequest->size,
                'lot_number' => $barcodeRequest->lot_number,
                'print_count' => $barcodeRequest->print_count,
                'plant' => $barcodeRequest->plant?->name,
                'location' => $barcodeRequest->location?->name,
            ],
        ]);
    }

    public function generate(Request $request, BarcodeRequest $barcodeRequest): JsonResponse
    {
        $barcodeRequest = $this->barcodeRequests->generate($request->user(), $barcodeRequest);

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil di-generate dan disetujui.',
            'barcode_material' => $barcodeRequest->generated_barcode_material,
            'full_barcode' => $barcodeRequest->full_barcode,
            'label_url' => route('admin.generate-barcode.label', $barcodeRequest),
        ], 201);
    }

    public function reject(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Aksi Reject tidak tersedia pada workflow barcode aktif.',
        ], 422);
    }

    public function label(Request $request, BarcodeRequest $barcodeRequest): Response
    {
        $requests = $this->barcodeRequests->printableByIds([$barcodeRequest->id]);
        $items = $this->barcodeRequests->buildPrintItems($requests, 280);
        $pdf = Pdf::loadView('admin.generate-barcode-label', [
            'items' => $items,
            'company' => config('sto.company_name'),
        ])->setPaper([0, 0, 283.46, 119.06], 'portrait');

        return $this->renderAndRecord(
            $request,
            $requests,
            $pdf->output(),
            "label-{$barcodeRequest->material_code}-{$barcodeRequest->lot_number}.pdf",
            'single',
        );
    }

    public function labelBulk(Request $request): Response
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct', 'exists:barcode_requests,id'],
        ]);
        $requests = $this->barcodeRequests->printableByIds($validated['ids']);
        $items = $this->barcodeRequests->buildPrintItems($requests, 280);
        $pdf = Pdf::loadView('admin.generate-barcode-label-bulk', [
            'items' => $items,
            'company' => config('sto.company_name'),
        ])->setPaper([0, 0, 283.46, 119.06], 'portrait');

        return $this->renderAndRecord(
            $request,
            $requests,
            $pdf->output(),
            'labels-bulk-'.now()->format('Ymd-His').'.pdf',
            'bulk',
        );
    }

    private function renderAndRecord(
        Request $request,
        $requests,
        string $pdfContent,
        string $fileName,
        string $printType,
    ): Response {
        $this->barcodeRequests->recordPrint($request->user(), $requests, $printType);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
