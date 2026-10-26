<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BarcodeRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BulkGenerateBarcodeController extends Controller
{
    public function __construct(private BarcodeRequestService $barcodeRequests) {}

    public function batchGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'distinct', 'exists:barcode_requests,id'],
        ]);
        $requests = $this->barcodeRequests->generateMany($request->user(), $validated['ids']);

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses {$requests->count()} request barcode.",
            'processed' => $requests->count(),
            'failed' => 0,
            'errors' => [],
        ]);
    }
}
