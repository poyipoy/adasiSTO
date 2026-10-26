<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\BarcodeDeleteHistory;
use App\Models\BarcodeRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\MaterialDoubleValidation;
use App\Models\Plant;
use App\Models\RackConfirmation;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Models\User;
use App\Services\ActiveStoService;
use App\Services\ActivityLogService;
use App\Services\BarcodeGeneratorService;
use App\Services\BarcodeRequestService;
use App\Services\QrGeneratorService;
use App\Services\ScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class IntegratedRevisionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $scanner;

    private User $validator;

    private Plant $plant;

    private Location $location;

    private MasterMaterial $material;

    private StoCode $stoCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Admin STO']);
        $this->scanner = User::factory()->scanner()->create(['name' => 'Operator 1']);
        $this->validator = User::factory()->validator()->create(['name' => 'Validator 1']);
        $this->stoCode = StoCode::create(['code' => 'STO2607', 'is_active' => true]);
        $this->plant = Plant::create(['name' => 'Cikarang', 'is_active' => true]);
        $this->location = Location::create([
            'plant_id' => $this->plant->id,
            'name' => 'CT01',
            'is_active' => true,
            'created_by_user_id' => $this->scanner->id,
        ]);
        $this->material = MasterMaterial::create([
            'material_code' => '1H',
            'material_name' => 'SKD11',
            'is_active' => true,
        ]);
    }

    public function test_generate_barcode_access_and_admin_only_add_barcode(): void
    {
        $this->actingAs($this->admin)->get('/admin/generate-barcode')->assertOk();
        $this->actingAs($this->validator)->get('/admin/generate-barcode')->assertOk();
        $this->actingAs($this->scanner)->get('/admin/generate-barcode')->assertForbidden();
        $inactiveValidator = User::factory()->validator()->create(['is_active' => false]);
        $this->actingAs($inactiveValidator)->get('/admin/generate-barcode')->assertForbidden();

        $payload = $this->barcodeRequestPayload(['shape_code' => 'RH', 'print_count' => 50]);

        $this->actingAs($this->validator)
            ->postJson('/admin/api/generate-barcode', $payload)
            ->assertForbidden();

        $this->actingAs($this->scanner)
            ->postJson('/admin/api/generate-barcode', $payload)
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->postJson('/admin/api/generate-barcode', $payload)
            ->assertCreated();

        $barcodeRequest = BarcodeRequest::firstOrFail();
        $this->assertSame($this->admin->id, $barcodeRequest->user_id);
        $this->assertSame(BarcodeRequest::STATUS_PENDING, $barcodeRequest->status);
        $this->assertSame(1, $barcodeRequest->qty);
        $this->assertSame(50, $barcodeRequest->print_count);
        $this->assertSame('HOLLOW', $barcodeRequest->shape_name);

        $this->actingAs($this->validator)
            ->postJson(route('admin.api.generate-barcode.reject', $barcodeRequest), [
                'rejection_reason' => 'Tidak digunakan',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Aksi Reject tidak tersedia pada workflow barcode aktif.');
        $this->assertSame(BarcodeRequest::STATUS_PENDING, $barcodeRequest->fresh()->status);

        $this->actingAs($this->validator)
            ->postJson(route('admin.api.generate-barcode.generate', $barcodeRequest))
            ->assertCreated()
            ->assertJsonPath('full_barcode', 'RH1H010-00200030B|LOT-ADD-001|1');
    }

    public function test_print_items_reuse_one_qr_per_request_and_print_reprint_are_audited(): void
    {
        $barcodeRequest = $this->createApprovedBarcodeRequest(['print_count' => 50]);
        $qrGenerator = Mockery::mock(QrGeneratorService::class);
        $qrGenerator->shouldReceive('generateDataUri')
            ->once()
            ->with($barcodeRequest->full_barcode, 200)
            ->andReturn('data:image/png;base64,dGVzdA==');

        $service = new BarcodeRequestService(
            app(ActiveStoService::class),
            app(BarcodeGeneratorService::class),
            $qrGenerator,
            app(ActivityLogService::class),
        );
        $requests = collect([$barcodeRequest]);

        $items = $service->buildPrintItems($requests, 200);
        $this->assertCount(50, $items);
        $this->assertSame(range(1, 50), $items->pluck('copy')->all());

        $service->recordPrint($this->validator, $requests, 'single');
        $service->recordPrint($this->admin, collect([$barcodeRequest->fresh()]), 'single');

        $barcodeRequest->refresh();
        $this->assertSame(BarcodeRequest::STATUS_PRINTED, $barcodeRequest->status);
        $this->assertSame($this->admin->id, $barcodeRequest->printed_by_user_id);
        $this->assertNotNull($barcodeRequest->printed_at);

        $printed = ActivityLog::where('action', 'barcode_request.printed')->firstOrFail();
        $reprinted = ActivityLog::where('action', 'barcode_request.reprinted')->firstOrFail();
        $this->assertSame(50, $printed->metadata['label_count']);
        $this->assertSame(50, $printed->metadata['total_labels']);
        $this->assertSame([$barcodeRequest->id], $printed->metadata['request_ids']);
        $this->assertSame($this->validator->id, $printed->user_id);
        $this->assertSame($this->admin->id, $reprinted->user_id);
    }

    public function test_rack_workflow_is_role_sequential_audited_and_isolated_per_active_sto(): void
    {
        $inactiveScanner = User::factory()->scanner()->create(['is_active' => false]);
        $this->actingAs($inactiveScanner)->get('/admin/rack-confirmation')->assertForbidden();

        $this->createScan($this->scanner, ['qty' => 7]);
        $this->createScan($this->scanner, [
            'barcode_raw' => 'RF1H011-00210031B|LOT-002|1',
            'barcode_material' => 'RF1H011-00210031B',
            'qty' => 4,
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.api.rack-confirmation.confirm', $this->location))
            ->assertUnprocessable();
        $this->actingAs($this->scanner)
            ->postJson(route('admin.api.rack-confirmation.cancel', $this->location), ['note' => 'Tidak sesuai'])
            ->assertForbidden();

        $this->actingAs($this->scanner)
            ->postJson(route('admin.api.rack-confirmation.finish', $this->location), ['note' => 'Selesai scan'])
            ->assertOk();
        $this->actingAs($this->admin)
            ->postJson(route('admin.api.rack-confirmation.confirm', $this->location))
            ->assertUnprocessable();
        $this->actingAs($this->scanner)
            ->postJson(route('admin.api.rack-confirmation.cancel', $this->location), ['note' => 'Tidak sesuai'])
            ->assertForbidden();
        $this->actingAs($this->scanner)
            ->postJson(route('admin.api.rack-confirmation.validate', $this->location))
            ->assertForbidden();
        $this->actingAs($this->validator)
            ->postJson(route('admin.api.rack-confirmation.validate', $this->location), ['note' => 'Valid'])
            ->assertOk();
        $this->actingAs($this->validator)
            ->postJson(route('admin.api.rack-confirmation.cancel', $this->location), ['note' => 'Tidak sesuai'])
            ->assertForbidden();
        $this->actingAs($this->validator)
            ->postJson(route('admin.api.rack-confirmation.confirm', $this->location))
            ->assertForbidden();
        $this->actingAs($this->admin)
            ->postJson(route('admin.api.rack-confirmation.confirm', $this->location), ['note' => 'Confirmed'])
            ->assertOk();

        $this->actingAs($this->admin)
            ->postJson(route('admin.api.rack-confirmation.cancel', $this->location), ['note' => 'x'])
            ->assertUnprocessable();
        $this->actingAs($this->admin)
            ->postJson(route('admin.api.rack-confirmation.cancel', $this->location), ['note' => 'Hitung ulang rack'])
            ->assertOk();

        $this->assertDatabaseHas('rack_confirmations', [
            'sto_code_id' => $this->stoCode->id,
            'location_id' => $this->location->id,
            'status' => RackConfirmation::STATUS_PENDING,
        ]);
        $this->assertDatabaseCount('rack_confirmation_histories', 4);

        $datatable = $this->actingAs($this->admin)
            ->getJson('/admin/api/rack-confirmation?draw=1&start=0&length=25');
        $datatable->assertOk()
            ->assertJsonPath('data.0.total_barcode', 2)
            ->assertJsonPath('data.0.rack_status', RackConfirmation::STATUS_PENDING);

        $nextSto = StoCode::create(['code' => 'STO2608', 'is_active' => false]);
        app(ActiveStoService::class)->activate($nextSto, $this->admin);

        $this->actingAs($this->admin)
            ->getJson('/admin/api/rack-confirmation?draw=1&start=0&length=25')
            ->assertOk()
            ->assertJsonPath('data.0.total_barcode', 0)
            ->assertJsonPath('data.0.rack_status', RackConfirmation::STATUS_PENDING);

        $this->assertDatabaseHas('rack_confirmations', [
            'sto_code_id' => $this->stoCode->id,
            'location_id' => $this->location->id,
        ]);
        $this->assertDatabaseMissing('rack_confirmations', [
            'sto_code_id' => $nextSto->id,
            'location_id' => $this->location->id,
        ]);
    }

    public function test_validator_can_finish_then_validate_a_pending_rack(): void
    {
        $validatorLocation = Location::create([
            'plant_id' => $this->plant->id,
            'name' => 'CT-VALIDATOR',
            'is_active' => true,
            'created_by_user_id' => $this->validator->id,
        ]);

        $this->actingAs($this->validator)
            ->postJson(route('admin.api.rack-confirmation.finish', $validatorLocation), ['note' => 'Scan selesai'])
            ->assertOk();

        $this->assertDatabaseHas('rack_confirmations', [
            'sto_code_id' => $this->stoCode->id,
            'location_id' => $validatorLocation->id,
            'status' => RackConfirmation::STATUS_FINISHED,
        ]);

        $this->actingAs($this->validator)
            ->postJson(route('admin.api.rack-confirmation.validate', $validatorLocation), ['note' => 'Data valid'])
            ->assertOk();

        $this->assertDatabaseHas('rack_confirmations', [
            'sto_code_id' => $this->stoCode->id,
            'location_id' => $validatorLocation->id,
            'status' => RackConfirmation::STATUS_VALIDATED,
        ]);
    }

    public function test_barcode_validation_and_rack_views_expose_the_hardened_ui_contract(): void
    {
        $generateBarcode = $this->actingAs($this->admin)
            ->get(route('admin.generate-barcode'));

        $generateBarcode->assertOk()
            ->assertSee('barcode-validation-summary', false)
            ->assertSee('barcode-validation-meta', false)
            ->assertSee('barcode-validation-check', false)
            ->assertSee('escapeValidationHtml', false)
            ->assertDontSee('✅', false)
            ->assertDontSee('❌', false);

        $rackConfirmation = $this->actingAs($this->admin)
            ->get(route('admin.rack-confirmation'));

        $rackConfirmation->assertOk()
            ->assertSee('Batalkan Konfirmasi Rack')
            ->assertSee('Ya, Batalkan')
            ->assertSee('data-rack-action', false)
            ->assertSee('Menunggu scanner menandai Finished.')
            ->assertSee('Menunggu scanner validator melakukan Validate.')
            ->assertSee('closeRackModal', false)
            ->assertSee('X-CSRF-TOKEN', false)
            ->assertSee('page-tab-label', false)
            ->assertSee('workspace-tab-counter', false)
            ->assertSee('readMenuPresentation', false)
            ->assertSee('title="1 notifikasi"', false)
            ->assertSeeInOrder(['STO Result', 'title="Konfirmasi Rak"', 'Master Data'], false)
            ->assertDontSee("find('span').text().trim()", false)
            ->assertDontSee('onclick="openTransition', false)
            ->assertDontSee('onclick="openCancel', false);
    }

    public function test_all_remaining_location_dropdowns_use_searchable_location_component(): void
    {
        $scanHistory = $this->actingAs($this->scanner)->get(route('scan.history'));
        $scanHistory->assertOk()
            ->assertSee('id="locationFilter"', false)
            ->assertSee('data-searchable-location', false)
            ->assertSee('StoSearchableLocationSelect', false);

        $deleteHistory = $this->actingAs($this->admin)->get(route('history.delete-barcode'));
        $deleteHistory->assertOk()
            ->assertSee('id="historyLocation"', false)
            ->assertSee('data-searchable-location', false);

        $generateBarcode = $this->actingAs($this->admin)->get(route('admin.generate-barcode'));
        $generateBarcode->assertOk()
            ->assertSee('id="addLocation"', false)
            ->assertSee('data-searchable-location', false)
            ->assertSee('Cari lokasi...', false)
            ->assertDontSee('Cetak Grid 3x3', false)
            ->assertDontSee('btnBulkPrintGrid', false)
            ->assertDontSee('bulkPrintGrid', false);

        $this->assertFalse(app('router')->has('admin.generate-barcode.batch-print-grid'));
    }

    public function test_generate_barcode_location_endpoint_returns_all_active_locations_for_selected_plant(): void
    {
        config(['sto.admin_filter_options_limit' => 1]);

        $secondLocation = Location::create([
            'plant_id' => $this->plant->id,
            'name' => 'CT02',
            'is_active' => true,
            'created_by_user_id' => $this->admin->id,
        ]);
        Location::create([
            'plant_id' => $this->plant->id,
            'name' => 'CT99 Inactive',
            'is_active' => false,
            'created_by_user_id' => $this->admin->id,
        ]);
        $otherPlant = Plant::create(['name' => 'Karawang', 'is_active' => true]);
        Location::create([
            'plant_id' => $otherPlant->id,
            'name' => 'KW01',
            'is_active' => true,
            'created_by_user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.api.generate-barcode.locations', ['plant_id' => $this->plant->id]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $this->location->id, 'name' => 'CT01'])
            ->assertJsonFragment(['id' => $secondLocation->id, 'name' => 'CT02'])
            ->assertJsonMissing(['name' => 'CT99 Inactive'])
            ->assertJsonMissing(['name' => 'KW01']);

        $this->actingAs($this->validator)
            ->getJson(route('admin.api.generate-barcode.locations', ['plant_id' => $this->plant->id]))
            ->assertOk();
        $this->actingAs($this->scanner)
            ->getJson(route('admin.api.generate-barcode.locations', ['plant_id' => $this->plant->id]))
            ->assertForbidden();
    }

    public function test_delete_history_is_persistent_role_scoped_and_read_only(): void
    {
        $ownScan = $this->createScan($this->scanner);
        $otherScanner = User::factory()->scanner()->create(['name' => 'Operator 2']);
        $otherScan = $this->createScan($otherScanner, [
            'barcode_raw' => 'RF1H012-00220032B|LOT-OTHER|1',
            'barcode_material' => 'RF1H012-00220032B',
        ]);

        $this->actingAs($this->scanner)->deleteJson('/api/scan/'.$ownScan->id)->assertOk();
        $this->actingAs($this->admin)->deleteJson('/admin/api/scan-results/'.$otherScan->id)->assertOk();

        $this->assertDatabaseHas('barcode_delete_histories', [
            'original_scan_result_id' => $ownScan->id,
            'deletion_source' => 'scan_history',
            'deleted_by_user_id' => $this->scanner->id,
        ]);
        $this->assertDatabaseHas('barcode_delete_histories', [
            'original_scan_result_id' => $otherScan->id,
            'deletion_source' => 'admin_scan_results',
            'deleted_by_user_id' => $this->admin->id,
            'original_user_id' => $otherScanner->id,
        ]);

        $this->actingAs($this->scanner)
            ->getJson('/api/history/delete-barcode?draw=1&start=0&length=25')
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['barcode_material' => $ownScan->barcode_material])
            ->assertJsonMissing(['barcode_material' => $otherScan->barcode_material]);

        $this->actingAs($otherScanner)
            ->getJson('/api/history/delete-barcode?draw=1&start=0&length=25')
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['barcode_material' => $otherScan->barcode_material]);

        $this->actingAs($this->admin)
            ->getJson('/api/history/delete-barcode?draw=1&start=0&length=25')
            ->assertOk()
            ->assertJsonPath('recordsFiltered', 2);

        $history = BarcodeDeleteHistory::where('original_scan_result_id', $ownScan->id)->firstOrFail();
        $this->actingAs($otherScanner)
            ->getJson('/api/history/delete-barcode/'.$history->id)
            ->assertForbidden();
        $this->actingAs($this->scanner)
            ->getJson('/api/history/delete-barcode/'.$history->id)
            ->assertOk()
            ->assertJsonPath('data.snapshot.barcode_material', $ownScan->barcode_material);

        try {
            $history->update(['material_name' => 'Changed']);
            $this->fail('History delete barcode seharusnya tidak dapat diubah.');
        } catch (LogicException) {
            $this->assertSame('SKD11', $history->fresh()->material_name);
        }

        try {
            $history->delete();
            $this->fail('History delete barcode seharusnya tidak dapat dihapus.');
        } catch (LogicException) {
            $this->assertDatabaseHas('barcode_delete_histories', ['id' => $history->id]);
        }
    }

    public function test_delete_rolls_back_when_strict_activity_audit_fails(): void
    {
        $scan = $this->createScan($this->scanner);
        $activityLog = Mockery::mock(ActivityLogService::class);
        $activityLog->shouldReceive('recordOrFail')
            ->once()
            ->andThrow(new RuntimeException('Audit unavailable'));
        $this->app->instance(ActivityLogService::class, $activityLog);
        $this->app->forgetInstance(ScanService::class);

        try {
            app(ScanService::class)->deleteForScanner($this->scanner, $scan->id);
            $this->fail('Delete seharusnya rollback ketika strict audit gagal.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit unavailable', $exception->getMessage());
        }

        $this->assertDatabaseHas('scan_results', ['id' => $scan->id]);
        $this->assertDatabaseMissing('barcode_delete_histories', ['original_scan_result_id' => $scan->id]);
        $this->assertDatabaseMissing('scan_result_logs', [
            'scan_result_id' => $scan->id,
            'action' => 'deleted',
        ]);
    }

    public function test_new_scan_only_clears_material_double_validation_for_active_sto(): void
    {
        MaterialDoubleValidation::create([
            'sto_code_id' => $this->stoCode->id,
            'barcode_material' => 'RF1H010-00200030B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'validated_by' => $this->validator->id,
            'validated_at' => now(),
        ]);

        $nextSto = StoCode::create(['code' => 'STO2608', 'is_active' => false]);
        app(ActiveStoService::class)->activate($nextSto, $this->admin);
        MaterialDoubleValidation::create([
            'sto_code_id' => $nextSto->id,
            'barcode_material' => 'RF1H010-00200030B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'validated_by' => $this->validator->id,
            'validated_at' => now(),
        ]);

        $this->actingAs($this->scanner)
            ->postJson('/api/scan/store', [
                'qr' => 'RF1H010-00200030B|LOT-NEW|1',
                'plant_id' => $this->plant->id,
                'location_id' => $this->location->id,
                'scan_source' => 'scanner_gun',
            ])
            ->assertOk();

        $this->assertDatabaseHas('material_double_validations', [
            'sto_code_id' => $this->stoCode->id,
            'barcode_material' => 'RF1H010-00200030B',
        ]);
        $this->assertDatabaseMissing('material_double_validations', [
            'sto_code_id' => $nextSto->id,
            'barcode_material' => 'RF1H010-00200030B',
        ]);
    }

    public function test_material_double_partial_delete_returns_group_to_need_check_and_keeps_one_row_minimum(): void
    {
        $first = $this->createScan($this->scanner);
        $second = $this->createScan($this->scanner, [
            'barcode_raw' => 'RF1H010-00200030B|LOT-002|1',
            'lot_number' => 'LOT-002',
        ]);
        $third = $this->createScan($this->scanner, [
            'barcode_raw' => 'RF1H010-00200030B|LOT-003|1',
            'lot_number' => 'LOT-003',
        ]);
        $group = [
            'barcode_material' => 'RF1H010-00200030B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
        ];

        $this->actingAs($this->validator)
            ->postJson('/admin/api/material-double/validate', $group)
            ->assertOk();

        $this->actingAs($this->validator)
            ->deleteJson('/admin/api/material-double/delete-selected', $group + ['ids' => [$first->id]])
            ->assertOk()
            ->assertJsonPath('deleted_count', 1)
            ->assertJsonPath('message', 'Data duplicate terpilih berhasil dihapus. Status tetap Need Check sampai tombol Valid digunakan.');

        $this->assertDatabaseMissing('material_double_validations', [
            'sto_code_id' => $this->stoCode->id,
            'barcode_material' => $group['barcode_material'],
            'plant_id' => $group['plant_id'],
            'location_id' => $group['location_id'],
        ]);
        $this->actingAs($this->validator)
            ->getJson('/admin/api/material-double?draw=1&start=0&length=25')
            ->assertOk()
            ->assertJsonPath('data.0.duplicate_count', 2)
            ->assertJsonPath('data.0.is_validated', false);

        $this->actingAs($this->validator)
            ->deleteJson('/admin/api/material-double/delete-selected', $group + ['ids' => [$second->id, $third->id]])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Anda tidak bisa menghapus semua data sekaligus. Sisakan minimal 1 data utama.');

        $this->assertDatabaseHas('scan_results', ['id' => $second->id]);
        $this->assertDatabaseHas('scan_results', ['id' => $third->id]);
    }

    public function test_location_nullable_fields_and_material_summary_totals_are_consistent(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/admin/api/master-location', [
                'name' => 'CT02',
                'plant_id' => $this->plant->id,
                'is_active' => true,
            ])
            ->assertOk();

        $createdLocation = Location::where('name', 'CT02')->firstOrFail();
        $this->assertNull($createdLocation->old_location_name);
        $this->assertNull($createdLocation->description);
        $this->assertNull($createdLocation->warehouse);
        $this->assertSame($this->admin->id, $createdLocation->created_by_user_id);

        $this->createScan($this->scanner, ['qty' => 2]);
        $this->createScan($this->scanner, ['qty' => 3]);

        $adminSummary = $this->actingAs($this->admin)
            ->getJson('/admin/api/material-summary?draw=1&start=0&length=25');
        $adminSummary->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.qty_total', 5)
            ->assertJsonPath('data.0.scan_count', 2)
            ->assertJsonPath('data.0.shape_name', 'FLAT');

        $validatorSummary = $this->actingAs($this->validator)
            ->getJson('/api/scan/material-summary?draw=1&start=0&length=25');
        $validatorSummary->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.qty_total', 5)
            ->assertJsonPath('data.0.scan_count', 2)
            ->assertJsonPath('data.0.shape_name', 'FLAT');
    }

    public function test_admin_can_delete_unused_location_with_pristine_rack_placeholder(): void
    {
        $location = Location::create([
            'plant_id' => $this->plant->id,
            'name' => 'Temporary Rack',
            'is_active' => true,
            'created_by_user_id' => $this->admin->id,
        ]);
        $confirmation = RackConfirmation::create([
            'sto_code_id' => $this->stoCode->id,
            'location_id' => $location->id,
            'status' => RackConfirmation::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson("/admin/api/master-location/{$location->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('rack_confirmations', ['id' => $confirmation->id]);
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_location_with_rack_confirmation_history_cannot_be_deleted(): void
    {
        $this->actingAs($this->scanner)
            ->postJson("/admin/api/rack-confirmation/{$this->location->id}/finish")
            ->assertOk();

        $this->actingAs($this->admin)
            ->deleteJson("/admin/api/master-location/{$this->location->id}")
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'Lokasi tidak dapat dihapus karena memiliki riwayat Konfirmasi Rak. Nonaktifkan lokasi untuk menjaga histori audit.'
            );

        $this->assertDatabaseHas('locations', ['id' => $this->location->id]);
        $this->assertDatabaseHas('rack_confirmation_histories', [
            'location_id' => $this->location->id,
            'to_status' => RackConfirmation::STATUS_FINISHED,
        ]);
    }

    public function test_location_used_by_scan_in_previous_sto_cannot_be_deleted(): void
    {
        $scan = $this->createScan($this->scanner);
        $this->stoCode->update(['is_active' => false]);
        StoCode::create(['code' => 'STO2608', 'is_active' => true]);

        $this->actingAs($this->admin)
            ->deleteJson("/admin/api/master-location/{$this->location->id}")
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'Lokasi tidak dapat dihapus karena sudah digunakan untuk scan. Nonaktifkan lokasi jika tidak lagi digunakan.'
            );

        $this->assertDatabaseHas('locations', ['id' => $this->location->id]);
        $this->assertDatabaseHas('scan_results', ['id' => $scan->id]);
    }

    public function test_location_used_by_barcode_request_cannot_be_deleted(): void
    {
        $barcodeRequest = $this->createApprovedBarcodeRequest();

        $this->actingAs($this->admin)
            ->deleteJson("/admin/api/master-location/{$this->location->id}")
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'message',
                'Lokasi tidak dapat dihapus karena sudah digunakan pada Request Barcode. Nonaktifkan lokasi jika tidak lagi digunakan.'
            );

        $this->assertDatabaseHas('locations', ['id' => $this->location->id]);
        $this->assertDatabaseHas('barcode_requests', ['id' => $barcodeRequest->id]);
    }

    private function barcodeRequestPayload(array $overrides = []): array
    {
        return array_merge([
            'material_code' => $this->material->material_code,
            'shape_code' => 'RF',
            'thickness' => 10,
            'width' => 20,
            'diameter' => null,
            'length' => 30,
            'lot_number' => 'LOT-ADD-001',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'print_count' => 1,
        ], $overrides);
    }

    private function createApprovedBarcodeRequest(array $overrides = []): BarcodeRequest
    {
        return BarcodeRequest::create(array_merge([
            'user_id' => $this->scanner->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'material_code' => '1H',
            'material_name' => 'SKD11',
            'shape_code' => 'RF',
            'shape_name' => 'FLAT',
            'thickness' => 10,
            'width' => 20,
            'diameter' => null,
            'length' => 30,
            'lot_number' => 'LOT-PRINT-001',
            'qty' => 1,
            'print_count' => 1,
            'status' => BarcodeRequest::STATUS_APPROVED,
            'generated_barcode_material' => 'RF1H010-00200030B',
            'reviewed_by_user_id' => $this->admin->id,
            'reviewed_at' => now(),
        ], $overrides));
    }

    private function createScan(User $user, array $overrides = []): ScanResult
    {
        return ScanResult::create(array_merge([
            'user_id' => $user->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'sto_code' => $this->stoCode->code,
            'barcode_raw' => 'RF1H010-00200030B|LOT-001|1',
            'barcode_material' => 'RF1H010-00200030B',
            'lot_number' => 'LOT-001',
            'qty' => 1,
            'material_code' => '1H',
            'material_name' => 'SKD11',
            'shape_code' => 'RF',
            'shape_name' => 'Flat',
            'thickness' => 10,
            'width' => 20,
            'diameter' => null,
            'length' => 30,
            'keterangan' => 'OK',
            'scan_source' => 'manual',
        ], $overrides));
    }
}
