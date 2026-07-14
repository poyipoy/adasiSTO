<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarcodeRequest;
use App\Services\ActivityLogService;
use App\Services\BarcodeGeneratorService;
use App\Services\QrGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BulkGenerateBarcodeController extends Controller
{
    public function __construct(
        private BarcodeGeneratorService $generator,
        private QrGeneratorService $qrService,
        private ActivityLogService $activityLog,
    ) {}

    /**
     * Batch approve and generate barcodes for multiple pending requests.
     */
    public function batchGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'exists:barcode_requests,id'],
            'qty'   => ['nullable', 'integer', 'min:1', 'max:99999'],
        ]);
        $validated['qty'] = 1;

        $requests = BarcodeRequest::whereIn('id', $validated['ids'])
            ->where('status', 'pending')
            ->get();

        if ($requests->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada request berstatus pending yang valid untuk diproses.',
            ], 422);
        }

        $successCount = 0;
        $failedCount  = 0;
        $errors       = [];

        foreach ($requests as $barcodeRequest) {
            $result = $this->generator->build([
                'shape_code'    => $barcodeRequest->shape_code,
                'material_code' => $barcodeRequest->material_code,
                'thickness'     => $barcodeRequest->thickness,
                'width'         => $barcodeRequest->width,
                'diameter'      => $barcodeRequest->diameter,
                'length'        => $barcodeRequest->length,
            ]);

            if (!$result['valid']) {
                $failedCount++;
                $errors[] = "ID #{$barcodeRequest->id} ({$barcodeRequest->material_code}): " . implode('; ', $result['errors']);
                continue;
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
                    'barcode_request.batch_generated',
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
                $successCount++;
            } catch (Throwable $e) {
                DB::rollBack();
                $failedCount++;
                Log::error('BulkGenerateBarcodeController@batchGenerate failed', [
                    'barcode_request_id' => $barcodeRequest->id,
                    'exception'          => $e->getMessage(),
                ]);
            }
        }

        // Real-time cache invalidation (Pilar 2)
        Cache::forget('scan_overview:*');
        Cache::forget('sidebar_badges');

        return response()->json([
            'success' => $successCount > 0,
            'message' => "Berhasil memproses {$successCount} request barcode" . ($failedCount > 0 ? " ({$failedCount} gagal)." : '.'),
            'processed' => $successCount,
            'failed'    => $failedCount,
            'errors'    => $errors,
        ], $successCount > 0 ? 200 : 422);
    }

    /**
     * Print batch QR labels in a 3x3 grid on A4 pages.
     */
    public function batchPrintA4Grid(Request $request): Response
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

        $items = $requests->map(function (BarcodeRequest $r) {
            return [
                'request'   => $r,
                'qrDataUri' => $this->qrService->generateDataUri($r->full_barcode, 220),
            ];
        });

        // Group into pages of 9 items (3x3 grid)
        $pages = $items->chunk(9);

        $pdf = Pdf::loadView('admin.generate-barcode-label-a4-grid', [
            'pages'   => $pages,
            'company' => config('sto.company_name'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("labels-3x3-grid-" . now()->format('Ymd-His') . ".pdf");
    }
}
