<?php

namespace Tests\Feature;

use App\Jobs\ExportScanResultsJob;
use App\Jobs\RecalculateScanSummaryJob;
use App\Models\ExportRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ActiveStoService;
use App\Services\ExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchitectureRequirementsTest extends TestCase
{
    use RefreshDatabase;

    private User $scanner;
    private User $otherScanner;
    private User $admin;
    private Plant $plant;
    private Location $location;
    private StoCode $stoCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scanner = User::factory()->scanner()->create();
        $this->otherScanner = User::factory()->scanner()->create();
        $this->admin = User::factory()->admin()->create();
        $this->stoCode = StoCode::create(['code' => 'STO2606', 'is_active' => true]);
        $this->plant = Plant::create(['name' => 'Cikarang', 'is_active' => true]);
        $this->location = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'CT01',
            'is_active' => true,
        ]);

        MasterMaterial::create(['material_code' => '1H', 'material_name' => 'SKD11', 'is_active' => true]);
        MasterMaterial::create(['material_code' => '2P', 'material_name' => 'SKD61', 'is_active' => true]);
    }

    public function test_active_sto_service_keeps_single_active_sto_and_writes_audit_log(): void
    {
        $newSto = StoCode::create(['code' => 'STO2607', 'is_active' => false]);

        app(ActiveStoService::class)->activate($newSto, $this->admin);

        $this->assertTrue($newSto->fresh()->is_active);
        $this->assertFalse($this->stoCode->fresh()->is_active);
        $this->assertSame(1, StoCode::where('is_active', true)->count());
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'sto.activated',
            'subject_type' => StoCode::class,
            'subject_id' => $newSto->id,
        ]);
    }

    public function test_health_endpoint_returns_safe_json_checks(): void
    {
        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.app.status', 'ok')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.storage.status', 'ok')
            ->assertJsonPath('checks.queue.status', 'ok');

        $this->assertArrayNotHasKey('environment', $response->json('checks.app'));
    }

    public function test_health_endpoint_can_expose_environment_only_when_configured(): void
    {
        config(['sto.health_expose_environment' => true]);

        $response = $this->getJson('/health');

        $response->assertOk()
            ->assertJsonPath('checks.app.environment', 'testing');
    }

    public function test_scan_store_uses_config_default_keterangan_and_writes_activity_log(): void
    {
        config(['sto.default_keterangan' => 'OK_FROM_CONFIG']);

        $response = $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->scanPayload());

        $response->assertOk()
            ->assertJsonPath('data.keterangan', 'OK_FROM_CONFIG');

        $scan = ScanResult::firstOrFail();

        $this->assertDatabaseHas('scan_results', [
            'id' => $scan->id,
            'keterangan' => 'OK_FROM_CONFIG',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->scanner->id,
            'action' => 'scan.created',
            'subject_type' => ScanResult::class,
            'subject_id' => $scan->id,
        ]);
    }

    public function test_scan_delete_keeps_hard_delete_and_writes_activity_log(): void
    {
        $scan = $this->createScan($this->scanner);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/scan/{$scan->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('scan_results', ['id' => $scan->id]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->scanner->id,
            'action' => 'scan.deleted',
            'subject_type' => ScanResult::class,
            'subject_id' => $scan->id,
        ]);
    }

    public function test_scan_result_policy_enforces_scanner_ownership_and_admin_access(): void
    {
        $scan = $this->createScan($this->otherScanner);

        $this->assertFalse($this->scanner->can('delete', $scan));
        $this->assertTrue($this->otherScanner->can('delete', $scan));
        $this->assertTrue($this->admin->can('delete', $scan));
        $this->assertFalse($this->scanner->can('update', $scan));
        $this->assertTrue($this->admin->can('update', $scan));
    }

    public function test_material_summary_endpoint_uses_server_side_grouped_counts(): void
    {
        $this->createScan($this->scanner, 'RF1H059-00960099B|ST2605|1');
        $this->createScan($this->scanner, 'RF1H059-00960099B|ST2606|1');
        $this->createScan($this->scanner, 'RR2P051-00000835B|ST2605|2', [
            'barcode_material' => 'RR2P051-00000835B',
            'barcode_raw' => 'RR2P051-00000835B|ST2605|2',
            'lot_number' => 'ST2605',
            'qty' => 2,
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/api/material-summary?draw=1&start=0&length=10');

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_heavy_operation_jobs_can_be_dispatched(): void
    {
        Queue::fake();

        $exportRequest = $this->createExportRequest();

        ExportScanResultsJob::dispatch($exportRequest->id);
        RecalculateScanSummaryJob::dispatch(['sto_code' => 'STO2606'], $this->admin->id);

        Queue::assertPushed(ExportScanResultsJob::class, fn (ExportScanResultsJob $job) => $job->exportRequestId === $exportRequest->id);
        Queue::assertPushed(RecalculateScanSummaryJob::class);
    }

    public function test_async_export_endpoint_queues_request_and_status_lists_it(): void
    {
        Queue::fake();
        config(['sto.export_disk' => 'public']);

        $response = $this->actingAs($this->admin)->postJson('/admin/export/scan-results/excel', [
            'sto_code' => 'STO2606',
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.format', 'excel')
            ->assertJsonPath('data.status', ExportRequest::STATUS_QUEUED);

        $exportRequest = ExportRequest::firstOrFail();

        $this->assertDatabaseHas('export_requests', [
            'id' => $exportRequest->id,
            'user_id' => $this->admin->id,
            'report' => 'scan_results',
            'format' => 'excel',
            'status' => ExportRequest::STATUS_QUEUED,
            'file_disk' => 'public',
        ]);
        Queue::assertPushed(ExportScanResultsJob::class, fn (ExportScanResultsJob $job) => $job->exportRequestId === $exportRequest->id);

        $status = $this->actingAs($this->admin)->getJson('/admin/export/scan-results/status');

        $status->assertOk()
            ->assertJsonPath('data.0.id', $exportRequest->id)
            ->assertJsonPath('data.0.status', ExportRequest::STATUS_QUEUED);
    }

    public function test_legacy_export_get_endpoint_queues_request_instead_of_sync_download(): void
    {
        Queue::fake();
        config(['sto.export_disk' => 'public']);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/scan-results/pdf?sto_code=STO2606');

        $response->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.format', 'pdf')
            ->assertJsonPath('data.status', ExportRequest::STATUS_QUEUED);

        $exportRequest = ExportRequest::firstOrFail();

        $this->assertSame('pdf', $exportRequest->format);
        $this->assertSame(ExportRequest::STATUS_QUEUED, $exportRequest->status);
        $this->assertSame(['sto_code' => 'STO2606'], $exportRequest->filters);
        Queue::assertPushed(ExportScanResultsJob::class, fn (ExportScanResultsJob $job) => $job->exportRequestId === $exportRequest->id);
    }

    public function test_legacy_export_get_endpoint_redirects_browser_with_flash_message(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)
            ->get('/admin/export/scan-results/excel');

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Export sedang diproses. File akan tersedia saat status selesai.');
        $this->assertDatabaseHas('export_requests', [
            'user_id' => $this->admin->id,
            'report' => 'scan_results',
            'format' => 'excel',
            'status' => ExportRequest::STATUS_QUEUED,
        ]);
        Queue::assertPushed(ExportScanResultsJob::class);
    }

    public function test_export_job_generates_excel_file_and_marks_request_completed(): void
    {
        Storage::fake('local');
        $this->createScan($this->scanner);
        $exportRequest = $this->createExportRequest();

        (new ExportScanResultsJob($exportRequest->id))->handle(app(ExportService::class), app(ActivityLogService::class));

        $exportRequest->refresh();

        $this->assertSame(ExportRequest::STATUS_COMPLETED, $exportRequest->status);
        $this->assertSame(1, $exportRequest->total_rows);
        $this->assertNotNull($exportRequest->file_path);
        Storage::disk('local')->assertExists($exportRequest->file_path);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'export.scan_results.completed',
            'subject_type' => ExportRequest::class,
            'subject_id' => $exportRequest->id,
        ]);
    }

    public function test_completed_async_export_can_be_downloaded_by_owner(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/scan-results/test.xlsx', 'xlsx');

        $exportRequest = $this->createExportRequest([
            'status' => ExportRequest::STATUS_COMPLETED,
            'file_path' => 'exports/scan-results/test.xlsx',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/export/scan-results/{$exportRequest->id}/download");

        $response->assertOk()
            ->assertDownload('test.xlsx');
    }

    public function test_completed_async_export_cannot_be_downloaded_by_non_owner(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('exports/scan-results/test.xlsx', 'xlsx');
        $otherAdmin = User::factory()->admin()->create();

        $exportRequest = $this->createExportRequest([
            'status' => ExportRequest::STATUS_COMPLETED,
            'file_path' => 'exports/scan-results/test.xlsx',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($otherAdmin)
            ->get("/admin/export/scan-results/{$exportRequest->id}/download");

        $response->assertForbidden();
    }

    private function scanPayload(array $override = []): array
    {
        return array_merge([
            'qr' => 'RF1H059-00960099B|ST2605|1',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'scan_source' => 'manual',
        ], $override);
    }

    private function createScan(User $user, string $qr = 'RF1H059-00960099B|ST2605|1', array $override = []): ScanResult
    {
        $barcodeMaterial = explode('|', $qr)[0];
        $location = $user->is($this->scanner)
            ? $this->location
            : Location::firstOrCreate(
                ['user_id' => $user->id, 'plant_id' => $this->plant->id, 'name' => 'Rack ' . $user->id],
                ['is_active' => true]
            );

        return ScanResult::create(array_merge([
            'user_id' => $user->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_id' => $location->id,
            'sto_code' => $this->stoCode->code,
            'barcode_raw' => $qr,
            'barcode_material' => $barcodeMaterial,
            'lot_number' => 'ST2605',
            'qty' => 1,
            'material_code' => '1H',
            'material_name' => 'SKD11',
            'shape_code' => 'RF',
            'shape_name' => 'Flat',
            'thickness' => 59,
            'width' => 96,
            'diameter' => null,
            'length' => 99,
            'keterangan' => 'OK',
            'scan_source' => 'manual',
        ], $override));
    }

    private function createExportRequest(array $override = []): ExportRequest
    {
        return ExportRequest::create(array_merge([
            'user_id' => $this->admin->id,
            'report' => 'scan_results',
            'format' => 'excel',
            'status' => ExportRequest::STATUS_QUEUED,
            'filters' => ['sto_code' => 'STO2606'],
            'file_disk' => 'local',
            'file_name' => 'test.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'queued_at' => now(),
        ], $override));
    }
}
