<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BarcodeRequest;
use App\Models\MasterMaterial;
use App\Models\Plant;
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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
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

        if ($materialCode = $request->input('filter_material')) {
            $query->where('material_code', $materialCode);
        }

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('material_code', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%")
                    ->orWhere('shape_name', 'like', "%{$search}%");
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
            'label_xlsx_url'   => route('admin.generate-barcode.label-xlsx', $barcodeRequest->id),
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

        // Label size: 50mm × 20mm (landscape for thermal printer)
        $pdf->setPaper([0, 0, 141.73, 56.69], 'portrait'); // ~50mm × 20mm in points

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

        $pdf->setPaper([0, 0, 141.73, 56.69], 'portrait');

        return $pdf->download("labels-bulk-" . now()->format('Ymd-His') . ".pdf");
    }

    /**
     * Helper to construct worksheet with physical label layout cards and embedded QR drawings.
     */
    private function buildLabelCardsSheet(Spreadsheet $spreadsheet, $requests): array
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Labels QR');

        // PENTING: margin atas dan bawah disetel 0.06 inch (~1.5 mm) sebagai safety buffer,
        // dan margin kiri/kanan 0 mutlak. Gabungan tinggi baris 16.9mm + margin 3mm = 19.9mm,
        // pas di dalam batas printable area driver printer TSC sehingga TIDAK MUNCUL auto page-break.
        $sheet->getPageMargins()->setTop(0.06);
        $sheet->getPageMargins()->setBottom(0.06);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Orientasi potret. Fit-to-page DIMATIKAN (bukan fitToWidth=1) karena
        // scaling otomatis ikut mengubah tinggi baris efektif saat print -> ini
        // sumber drifting tambahan. Kita kunci scale = 100% dan andalkan row
        // height + paper size yang presisi.
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToPage(false);
        $sheet->getPageSetup()->setScale(100);

        // Lebar kolom tetap: A untuk QR (~15mm), B untuk teks (~34mm) = total 50mm
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(16);

        $r = 1;
        $qrPaths = [];
        $company = config('sto.company_name', 'PT Astra Daido Steel Indonesia');

        // Total tinggi 4 baris disetel 48.0pt (= 16.93mm).
        // Ditambah top & bottom margin (0.06" + 0.06" = 3.0mm), total tepat ~19.93mm.
        // Berada di bawah batas maksimal kertas 20mm dan printable area driver printer,
        // sehingga Excel DIJAMIN tidak memecah baris ke halaman berikutnya atau menarik baris berikutnya.
        $rowHeights = [10.5, 12.5, 12.5, 12.5]; // total = 48.0pt = 16.93mm persis

        // Hitung dulu jumlah label valid, supaya kita tahu kapan "label terakhir"
        // tercapai dan tidak memasang page break redundan setelahnya (yang bisa
        // membuat printer TSC feed 1 halaman kosong ekstra di akhir job print).
        $validRequests = $requests->filter(function ($request) {
            return $request->status === 'approved' && $request->generated_barcode_material;
        })->values();
        $totalLabels = $validRequests->count();
        $labelIndex = 0;

        foreach ($validRequests as $request) {
            $labelIndex++;

            // Row 1 of label block: Company Name
            $sheet->mergeCells("A{$r}:B{$r}");
            $sheet->setCellValue("A{$r}", $company);
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => [
                    'name'  => 'Arial',
                    'size'  => 7,
                    'bold'  => true,
                    'color' => ['argb' => 'FF000000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->getRowDimension($r)->setRowHeight($rowHeights[0]);

            // Row 2 of label block: Generated Barcode Material (Tinggi: 12 pt)
            $sheet->setCellValue("B" . ($r + 1), $request->generated_barcode_material);
            $sheet->getStyle("B" . ($r + 1))->applyFromArray([
                'font' => [
                    'name'  => 'Arial',
                    'size'  => 8.5,
                    'bold'  => true,
                    'color' => ['argb' => 'FF000000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'shrinkToFit' => true,
                ],
            ]);
            $sheet->getRowDimension($r + 1)->setRowHeight($rowHeights[1]);

            // Row 3 of label block: Lot Number (Tinggi: 12 pt)
            $sheet->setCellValue("B" . ($r + 2), $request->lot_number);
            $sheet->getStyle("B" . ($r + 2))->applyFromArray([
                'font' => [
                    'name'  => 'Arial',
                    'size'  => 8.5,
                    'bold'  => true,
                    'color' => ['argb' => 'FF000000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'shrinkToFit' => true,
                ],
            ]);
            $sheet->getRowDimension($r + 2)->setRowHeight($rowHeights[2]);

            // Row 4 of label block: Detail String / Dimensi (Tinggi: 12 pt)
            $detail = trim(strtoupper($request->material_name . ' ' . $request->label_description));
            $sheet->setCellValue("B" . ($r + 3), $detail);
            $sheet->getStyle("B" . ($r + 3))->applyFromArray([
                'font' => [
                    'name'  => 'Arial',
                    'size'  => 7.5,
                    'bold'  => true,
                    'color' => ['argb' => 'FF000000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'shrinkToFit' => true,
                ],
            ]);
            $sheet->getRowDimension($r + 3)->setRowHeight($rowHeights[3]);

            // Generate QR Code PNG file for Drawing (Ukuran: 36x36 px, pas di sel A2:A4)
            try {
                $qrPath = $this->qrService->generateFile($request->full_barcode, 200);
                if (file_exists($qrPath)) {
                    $qrPaths[] = $qrPath;
                    $drawing = new Drawing();
                    $drawing->setName('QR ' . $request->id);
                    $drawing->setDescription('QR Code');
                    $drawing->setPath($qrPath);
                    $drawing->setCoordinates('A' . ($r + 1));
                    $drawing->setOffsetX(4);
                    $drawing->setOffsetY(1);
                    $drawing->setWidth(36);
                    $drawing->setWorksheet($sheet);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed adding QR Drawing for label XLSX: ' . $e->getMessage());
            }


            // OOXML spec: <brk id="N"/> artinya break DI BAWAH baris N
            // (baris N adalah baris TERAKHIR halaman itu, baris N+1 mulai halaman baru).
            // Label 1 pakai baris r, r+1, r+2, r+3 → baris terakhir = r+3.
            // Jadi setBreak("A".(r+3)) → id=r+3 → break di bawah baris r+3 → baris r+3 masih
            // di halaman yang sama dengan r, r+1, r+2 → 4 baris per halaman. ✓
            // (Bug lama: r+4 → id=r+4 → baris r+4 ikut masuk halaman yang sama = header label berikutnya!)
            if ($labelIndex < $totalLabels) {
                $sheet->setBreak("A" . ($r + 3), \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
            }
            $r += 4;
        }

        // Print area eksplisit mencakup SEMUA baris yang dipakai (A1:B{lastRow}).
        // Ini mengunci Excel supaya tidak menghitung ulang printable-area dari
        // paper size default sistem - salah satu penyebab utama automatic break
        // bentrok dengan manual break di kasus kamu.
        $lastRow = $r - 1;
        if ($lastRow >= 1) {
            $sheet->getPageSetup()->setPrintArea("A1:B{$lastRow}");
        }

        return $qrPaths;
    }

    /**
     * Patch ukuran kertas custom (50 x 20 mm) langsung ke XML internal file .xlsx.
     *
     * PhpSpreadsheet TIDAK menyediakan API publik untuk paper size custom dalam
     * mm/inch (PageSetup hanya expose enum PAPERSIZE_A4, PAPERSIZE_LETTER, dst).
     * Namun format OOXML (.xlsx) MENDUKUNG atribut ini secara native lewat
     * elemen <pageSetup ... paperWidth="50mm" paperHeight="20mm" .../> di
     * xl/worksheets/sheet1.xml. Tanpa patch ini, Excel akan selalu memakai
     * paper size default sistem operasi untuk menghitung automatic page break -
     * itulah akar masalah baris 1 label berikutnya "nyasar" ke label sekarang.
     *
     * File .xlsx sebenarnya adalah ZIP archive, sehingga kita buka sebagai ZipArchive,
     * timpa isi sheet1.xml dengan atribut paperWidth/paperHeight, lalu simpan ulang.
     */
    private function patchXlsxPaperSize(string $filePath, float $widthMm = 50.0, float $heightMm = 20.0): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            Log::warning("Gagal membuka {$filePath} sebagai ZIP untuk patch paper size.");
            return;
        }

        // Cari nama sheet XML aktif (biasanya xl/worksheets/sheet1.xml)
        $sheetXmlName = 'xl/worksheets/sheet1.xml';
        $xml = $zip->getFromName($sheetXmlName);

        if ($xml === false) {
            Log::warning("Tidak menemukan {$sheetXmlName} di dalam {$filePath}.");
            $zip->close();
            return;
        }

        $widthAttr = number_format($widthMm, 2, '.', '') . 'mm';
        $heightAttr = number_format($heightMm, 2, '.', '') . 'mm';

        if (preg_match('/<pageSetup\b[^>]*\/>/', $xml)) {
            // Elemen pageSetup sudah ada (ditulis oleh PhpSpreadsheet) -> tambahkan
            // atribut paperWidth/paperHeight ke dalamnya, jangan bikin elemen baru.
            $xml = preg_replace_callback(
                '/<pageSetup\b([^>]*)\/>/',
                function (array $m) use ($widthAttr, $heightAttr) {
                    $attrs = $m[1];
                    // Buang paperSize/paperWidth/paperHeight lama supaya tidak dobel
                    // paperSize HARUS diganti ke "0" (custom) agar Excel mau baca
                    // paperWidth & paperHeight. Tanpa ini Excel pakai A4 default.
                    $attrs = preg_replace('/\s+paperSize="[^"]*"/', '', $attrs);
                    $attrs = preg_replace('/\s+paperWidth="[^"]*"/', '', $attrs);
                    $attrs = preg_replace('/\s+paperHeight="[^"]*"/', '', $attrs);
                    return '<pageSetup paperSize="0"' . $attrs . ' paperWidth="' . $widthAttr . '" paperHeight="' . $heightAttr . '"/>';
                },
                $xml
            );
        } else {
            // Elemen pageSetup belum ada -> sisipkan sebelum </worksheet>
            $pageSetupTag = '<pageSetup paperWidth="' . $widthAttr . '" paperHeight="' . $heightAttr . '" orientation="portrait" scale="100"/>';
            $xml = str_replace('</worksheet>', $pageSetupTag . '</worksheet>', $xml);
        }

        $zip->deleteName($sheetXmlName);
        $zip->addFromString($sheetXmlName, $xml);
        $zip->close();
    }

    /**
     * Download a single QR label as an Excel (.xlsx) file with embedded QR drawing and formatted card.
     */
    public function labelXlsx(BarcodeRequest $barcodeRequest): SymfonyResponse
    {
        abort_unless(
            $barcodeRequest->status === 'approved' && $barcodeRequest->generated_barcode_material,
            404,
            'Label hanya tersedia untuk request yang sudah di-approve.'
        );

        $spreadsheet = new Spreadsheet();
        $qrPaths = $this->buildLabelCardsSheet($spreadsheet, collect([$barcodeRequest]));

        $writer = new Xlsx($spreadsheet);
        $tempXlsx = tempnam(sys_get_temp_dir(), 'sto_xlsx_');
        $writer->save($tempXlsx);
        $this->patchXlsxPaperSize($tempXlsx, 50.0, 20.0);

        foreach ($qrPaths as $qrPath) {
            if (file_exists($qrPath)) {
                @unlink($qrPath);
            }
        }

        $filename = "label-{$barcodeRequest->material_code}-{$barcodeRequest->lot_number}.xlsx";

        return response()->streamDownload(function () use ($tempXlsx) {
            readfile($tempXlsx);
            @unlink($tempXlsx);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Download bulk QR labels as an Excel (.xlsx) file with embedded QR drawings and formatted cards.
     */
    public function labelBulkXlsx(Request $request): SymfonyResponse
    {
        if ($request->has('ids') && is_array($request->ids) && count($request->ids) > 0) {
            $requests = BarcodeRequest::with(['plant', 'location'])
                ->whereIn('id', $request->ids)
                ->where('status', 'approved')
                ->whereNotNull('generated_barcode_material')
                ->get();
        } else {
            // Fallback to active filters if no specific IDs checked
            $query = BarcodeRequest::with(['plant', 'location'])
                ->where('status', 'approved')
                ->whereNotNull('generated_barcode_material');

            if ($request->filled('filter_plant')) {
                $query->where('plant_id', $request->filter_plant);
            }
            if ($request->filled('filter_material')) {
                $query->where('material_code', $request->filter_material);
            }

            $requests = $query->get();
        }

        abort_if($requests->isEmpty(), 404, 'Tidak ada data label Approved yang valid untuk dicetak ke Excel.');

        $spreadsheet = new Spreadsheet();
        $qrPaths = $this->buildLabelCardsSheet($spreadsheet, $requests);

        $writer = new Xlsx($spreadsheet);
        $tempXlsx = tempnam(sys_get_temp_dir(), 'sto_xlsx_');
        $writer->save($tempXlsx);
        $this->patchXlsxPaperSize($tempXlsx, 50.0, 20.0);

        foreach ($qrPaths as $qrPath) {
            if (file_exists($qrPath)) {
                @unlink($qrPath);
            }
        }

        $filename = "labels-bulk-" . now()->format('Ymd-His') . ".xlsx";

        return response()->streamDownload(function () use ($tempXlsx) {
            readfile($tempXlsx);
            @unlink($tempXlsx);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

