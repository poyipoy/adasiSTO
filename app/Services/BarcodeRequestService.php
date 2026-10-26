<?php

namespace App\Services;

use App\Models\BarcodeRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BarcodeRequestService
{
    public function __construct(
        private ActiveStoService $activeStoService,
        private BarcodeGeneratorService $generator,
        private QrGeneratorService $qrGenerator,
        private ActivityLogService $activityLog,
    ) {}

    public function create(User $actor, array $payload, bool $adminCreated = false): BarcodeRequest
    {
        $activeSto = $this->activeStoService->active();
        if (! $activeSto) {
            throw ValidationException::withMessages(['sto' => ActiveStoService::NO_ACTIVE_STO_MESSAGE]);
        }

        $material = MasterMaterial::active()
            ->where('material_code', strtoupper($payload['material_code']))
            ->firstOrFail();
        $location = Location::active()
            ->whereKey($payload['location_id'])
            ->where('plant_id', $payload['plant_id'])
            ->firstOrFail();

        $shapeCode = strtoupper($payload['shape_code']);
        $barcodeRequest = DB::transaction(function () use ($actor, $payload, $adminCreated, $activeSto, $material, $location, $shapeCode) {
            User::query()->whereKey($actor->id)->lockForUpdate()->firstOrFail();

            $duplicateExists = BarcodeRequest::query()
                ->where('user_id', $actor->id)
                ->where('sto_code_id', $activeSto->id)
                ->where('plant_id', $payload['plant_id'])
                ->where('location_id', $location->id)
                ->where('material_code', $material->material_code)
                ->where('shape_code', $shapeCode)
                ->where('lot_number', trim($payload['lot_number']))
                ->where('status', BarcodeRequest::STATUS_PENDING)
                ->where('thickness', in_array($shapeCode, ['RF', 'RH'], true) ? $payload['thickness'] : null)
                ->where('width', in_array($shapeCode, ['RF', 'RH'], true) ? $payload['width'] : null)
                ->where('diameter', $shapeCode === 'RR' ? $payload['diameter'] : null)
                ->where('length', $payload['length'])
                ->exists();

            if ($duplicateExists) {
                throw ValidationException::withMessages([
                    'duplicate' => 'Request barcode pending dengan data yang sama sudah tersedia.',
                ]);
            }

            $barcodeRequest = BarcodeRequest::create([
                'user_id' => $actor->id,
                'sto_code_id' => $activeSto->id,
                'plant_id' => $payload['plant_id'],
                'location_id' => $location->id,
                'material_code' => $material->material_code,
                'material_name' => $material->material_name,
                'shape_code' => $shapeCode,
                'shape_name' => ShapeFormatter::name($shapeCode),
                'thickness' => in_array($shapeCode, ['RF', 'RH'], true) ? $payload['thickness'] : null,
                'width' => in_array($shapeCode, ['RF', 'RH'], true) ? $payload['width'] : null,
                'diameter' => $shapeCode === 'RR' ? $payload['diameter'] : null,
                'length' => $payload['length'],
                'lot_number' => trim($payload['lot_number']),
                'qty' => 1,
                'print_count' => $payload['print_count'],
                'status' => BarcodeRequest::STATUS_PENDING,
            ]);

            $this->activityLog->recordOrFail(
                $actor,
                $adminCreated ? 'barcode_request.admin_created' : 'barcode_request.created',
                $barcodeRequest,
                newValues: $barcodeRequest->toArray(),
            );

            return $barcodeRequest;
        });

        Cache::forget('sidebar_badges');

        return $barcodeRequest;
    }

    public function generate(User $actor, BarcodeRequest $barcodeRequest, string $activity = 'barcode_request.generated'): BarcodeRequest
    {
        $generated = DB::transaction(function () use ($actor, $barcodeRequest, $activity) {
            $barcodeRequest = BarcodeRequest::query()->lockForUpdate()->findOrFail($barcodeRequest->id);
            if ($barcodeRequest->status !== BarcodeRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => "Request sudah diproses dengan status {$barcodeRequest->status}.",
                ]);
            }

            $result = $this->generator->build($barcodeRequest->only([
                'shape_code', 'material_code', 'thickness', 'width', 'diameter', 'length',
            ]));
            if (! $result['valid']) {
                throw ValidationException::withMessages(['barcode' => $result['errors']]);
            }

            $oldValues = $barcodeRequest->only([
                'status', 'generated_barcode_material', 'qty', 'reviewed_by_user_id', 'reviewed_at',
            ]);
            $barcodeRequest->update([
                'status' => BarcodeRequest::STATUS_APPROVED,
                'generated_barcode_material' => $result['barcode_material'],
                'qty' => 1,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
            ]);

            $this->activityLog->recordOrFail(
                $actor,
                $activity,
                $barcodeRequest,
                $oldValues,
                $barcodeRequest->fresh()->only([
                    'status', 'generated_barcode_material', 'qty', 'reviewed_by_user_id', 'reviewed_at',
                ]),
            );

            return $barcodeRequest->fresh();
        });

        Cache::forget('sidebar_badges');

        return $generated;
    }

    public function generateMany(User $actor, array $ids): Collection
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->unique()->values();

        $generated = DB::transaction(function () use ($actor, $ids) {
            $requests = BarcodeRequest::query()
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($requests->count() !== $ids->count()) {
                throw ValidationException::withMessages(['ids' => 'Sebagian request barcode tidak ditemukan.']);
            }

            foreach ($ids as $id) {
                $request = $requests->get($id);
                if ($request->status !== BarcodeRequest::STATUS_PENDING) {
                    throw ValidationException::withMessages([
                        'status' => "Request #{$id} tidak lagi berstatus pending.",
                    ]);
                }

                $result = $this->generator->build($request->only([
                    'shape_code', 'material_code', 'thickness', 'width', 'diameter', 'length',
                ]));
                if (! $result['valid']) {
                    throw ValidationException::withMessages([
                        'barcode' => "Request #{$id}: ".implode('; ', $result['errors']),
                    ]);
                }

                $oldValues = $request->only(['status', 'generated_barcode_material', 'qty']);
                $request->update([
                    'status' => BarcodeRequest::STATUS_APPROVED,
                    'generated_barcode_material' => $result['barcode_material'],
                    'qty' => 1,
                    'reviewed_by_user_id' => $actor->id,
                    'reviewed_at' => now(),
                ]);
                $this->activityLog->recordOrFail(
                    $actor,
                    'barcode_request.batch_generated',
                    $request,
                    $oldValues,
                    $request->fresh()->only(['status', 'generated_barcode_material', 'qty']),
                );
            }

            return BarcodeRequest::query()->whereIn('id', $ids)->get();
        });

        Cache::forget('sidebar_badges');

        return $generated;
    }

    public function printableByIds(array $ids): Collection
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->unique()->values();
        $requests = BarcodeRequest::with(['plant', 'location'])
            ->whereIn('id', $ids)
            ->whereIn('status', [BarcodeRequest::STATUS_APPROVED, BarcodeRequest::STATUS_PRINTED])
            ->whereNotNull('generated_barcode_material')
            ->get()
            ->keyBy('id');

        if ($requests->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'ids' => 'Sebagian request belum approved, tidak ditemukan, atau tidak dapat dicetak.',
            ]);
        }

        return $ids->map(fn ($id) => $requests->get($id));
    }

    public function buildPrintItems(Collection $requests, int $qrSize): Collection
    {
        $items = collect();

        foreach ($requests as $barcodeRequest) {
            $qrDataUri = $this->qrGenerator->generateDataUri($barcodeRequest->full_barcode, $qrSize);
            for ($copy = 1; $copy <= $barcodeRequest->print_count; $copy++) {
                $items->push([
                    'request' => $barcodeRequest,
                    'qrDataUri' => $qrDataUri,
                    'copy' => $copy,
                ]);
            }
        }

        return $items;
    }

    public function recordPrint(User $actor, Collection $requests, string $printType): void
    {
        $ids = $requests->pluck('id')->map(fn ($id) => (int) $id)->values();
        $totalLabels = (int) $requests->sum('print_count');
        $batchUuid = (string) Str::uuid();

        DB::transaction(function () use ($actor, $ids, $totalLabels, $batchUuid, $printType) {
            $locked = BarcodeRequest::query()->whereIn('id', $ids)->lockForUpdate()->get();
            if ($locked->count() !== $ids->count()) {
                throw ValidationException::withMessages(['ids' => 'Data barcode berubah sebelum proses print selesai.']);
            }

            foreach ($locked as $barcodeRequest) {
                if (! in_array($barcodeRequest->status, [BarcodeRequest::STATUS_APPROVED, BarcodeRequest::STATUS_PRINTED], true)
                    || ! $barcodeRequest->generated_barcode_material) {
                    throw ValidationException::withMessages([
                        'status' => "Request #{$barcodeRequest->id} tidak dapat dicetak.",
                    ]);
                }

                $oldStatus = $barcodeRequest->status;
                $barcodeRequest->update([
                    'status' => BarcodeRequest::STATUS_PRINTED,
                    'printed_by_user_id' => $actor->id,
                    'printed_at' => now(),
                ]);

                $this->activityLog->recordOrFail(
                    $actor,
                    $oldStatus === BarcodeRequest::STATUS_PRINTED
                        ? 'barcode_request.reprinted'
                        : 'barcode_request.printed',
                    $barcodeRequest,
                    ['status' => $oldStatus],
                    ['status' => BarcodeRequest::STATUS_PRINTED],
                    [
                        'batch_uuid' => $batchUuid,
                        'print_type' => $printType,
                        'label_count' => $barcodeRequest->print_count,
                        'total_labels' => $totalLabels,
                        'request_ids' => $ids->all(),
                    ],
                );
            }
        });
    }
}
