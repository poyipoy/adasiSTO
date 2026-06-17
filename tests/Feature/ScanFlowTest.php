<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\MasterKeterangan;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\ScanResultLog;
use App\Models\StoCode;
use App\Models\User;
use App\Services\ScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ScanFlowTest extends TestCase
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
        $this->location = Location::create(['user_id' => $this->scanner->id, 'plant_id' => $this->plant->id, 'name' => 'CT01', 'is_active' => true]);
        MasterMaterial::create(['material_code' => '1H', 'material_name' => 'SKD11', 'is_active' => true]);
        MasterMaterial::create(['material_code' => '2P', 'material_name' => 'SKD61', 'is_active' => true]);
        foreach (['OK', 'Lot Salah', 'Size Salah', 'Material Salah'] as $name) {
            MasterKeterangan::create(['name' => $name, 'is_active' => true]);
        }
    }

    public function test_active_sto_required(): void
    {
        StoCode::query()->update(['is_active' => false]);

        $response = $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload());

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Tidak ada STO aktif yang tersedia. Silakan hubungi Admin.');
    }

    public function test_scanner_only_sees_own_history(): void
    {
        $ownScan = $this->createScan($this->scanner, 'RF1H059-00960099B|ST2605|1');
        $otherScan = $this->createScan($this->otherScanner, 'RF1H060-00970098B|ST2605|1');

        $response = $this->actingAs($this->scanner)->getJson('/api/scan/history');

        $response->assertOk()
            ->assertJsonFragment(['id' => $ownScan->id])
            ->assertJsonMissing(['id' => $otherScan->id]);
    }

    public function test_scanner_can_create_own_location_rack(): void
    {
        $response = $this->actingAs($this->scanner)->postJson('/api/locations', [
            'plant_id' => $this->plant->id,
            'name' => 'Rack A1',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Rack A1');

        $this->assertDatabaseHas('locations', [
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'Rack A1',
        ]);
    }

    public function test_setup_page_shows_location_rack_scanner_controls(): void
    {
        $response = $this->actingAs($this->scanner)->get('/scan/setup');

        $response->assertOk()
            ->assertSee('Scan QR / Barcode Rack')
            ->assertSee('showLocationCameraBtn')
            ->assertSee('locationReader', false)
            ->assertSee('html5-qrcode.min.js', false);
    }

    public function test_location_rack_list_only_shows_current_user_locations(): void
    {
        $otherLocation = Location::create([
            'user_id' => $this->otherScanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'Other Rack',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->scanner)->getJson("/api/locations?plant_id={$this->plant->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $this->location->id, 'name' => 'CT01'])
            ->assertJsonMissing(['id' => $otherLocation->id, 'name' => 'Other Rack']);
    }

    public function test_scanner_cannot_store_scan_using_other_user_location(): void
    {
        $otherLocation = Location::create([
            'user_id' => $this->otherScanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'Other Rack',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload([
            'location_id' => $otherLocation->id,
        ]));

        $response->assertStatus(422);
    }

    public function test_scanner_can_delete_own_unused_location_rack(): void
    {
        $location = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'Temporary Rack',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/locations/{$location->id}");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_scanner_cannot_delete_other_user_location_rack(): void
    {
        $otherLocation = Location::create([
            'user_id' => $this->otherScanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'Other Rack',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/locations/{$otherLocation->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('locations', ['id' => $otherLocation->id]);
    }

    public function test_scanner_cannot_delete_location_rack_used_by_scan(): void
    {
        $scan = $this->createScan($this->scanner);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/locations/{$this->location->id}");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('locations', ['id' => $this->location->id]);
        $this->assertDatabaseHas('scan_results', ['id' => $scan->id]);
    }

    public function test_scanner_can_hard_delete_own_scan_with_audit(): void
    {
        $scan = $this->createScan($this->scanner);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/scan/{$scan->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('scan_results', ['id' => $scan->id]);
        $this->assertDatabaseHas('scan_result_logs', [
            'scan_result_id' => $scan->id,
            'user_id' => $this->scanner->id,
            'action' => 'deleted',
        ]);
    }

    public function test_scanner_cannot_delete_other_user_scan(): void
    {
        $scan = $this->createScan($this->otherScanner);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/scan/{$scan->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('scan_results', ['id' => $scan->id]);
    }

    public function test_scanner_cannot_edit_keterangan(): void
    {
        $scan = $this->createScan($this->scanner);

        $response = $this->actingAs($this->scanner)->putJson("/admin/api/scan-results/{$scan->id}", [
            'keterangan' => 'Lot Salah',
            'plant_id' => $this->plant->id,
            'location_name' => 'CT02',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('scan_results', ['id' => $scan->id, 'keterangan' => 'OK']);
    }

    public function test_admin_can_edit_keterangan(): void
    {
        $scan = $this->createScan($this->scanner);

        $response = $this->actingAs($this->admin)->putJson("/admin/api/scan-results/{$scan->id}", $this->adminPayload([
            'keterangan' => 'Lot Salah',
            'plant_id' => $this->plant->id,
            'location_name' => 'CT02',
        ]));

        $response->assertOk();
        $this->assertDatabaseHas('scan_results', ['id' => $scan->id, 'keterangan' => 'Lot Salah']);
        $this->assertDatabaseHas('locations', [
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'CT02',
        ]);
        $this->assertDatabaseHas('scan_result_logs', [
            'scan_result_id' => $scan->id,
            'user_id' => $this->admin->id,
            'action' => 'updated',
            'field_name' => 'keterangan',
        ]);
    }

    public function test_admin_can_manually_create_scan_result(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/api/scan-results', $this->adminPayload([
            'location_name' => 'Admin Rack',
            'scan_source' => 'admin',
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.barcode_material', 'RF1H059-00960099B')
            ->assertJsonPath('data.location', 'Admin Rack');

        $this->assertDatabaseHas('scan_results', [
            'user_id' => $this->scanner->id,
            'sto_code_id' => $this->stoCode->id,
            'barcode_material' => 'RF1H059-00960099B',
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
        ]);
    }

    public function test_admin_manual_create_duplicate_returns_warning_then_can_force_save(): void
    {
        $this->actingAs($this->admin)->postJson('/admin/api/scan-results', $this->adminPayload())->assertCreated();

        $response = $this->actingAs($this->admin)->postJson('/admin/api/scan-results', $this->adminPayload());

        $response->assertStatus(409)
            ->assertJsonPath('duplicate', true);

        $force = $this->actingAs($this->admin)->postJson('/admin/api/scan-results', $this->adminPayload(['force_save' => true]));

        $force->assertCreated();
        $this->assertSame(2, ScanResult::where('sto_code', 'STO2606')->where('barcode_material', 'RF1H059-00960099B')->count());
    }

    public function test_admin_can_edit_all_scan_result_columns_manually(): void
    {
        $scan = $this->createScan($this->scanner);
        $newSto = StoCode::create(['code' => 'STO2607', 'is_active' => false]);

        $response = $this->actingAs($this->admin)->putJson("/admin/api/scan-results/{$scan->id}", $this->adminPayload([
            'user_id' => $this->otherScanner->id,
            'sto_code_id' => $newSto->id,
            'location_name' => 'Round Rack',
            'barcode_raw' => 'RR2P051-00000835B|LOTEDIT|7',
            'barcode_material' => 'RR2P051-00000835B',
            'lot_number' => 'LOTEDIT',
            'qty' => 7,
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
            'keterangan' => 'Size Salah',
            'scan_source' => 'admin_edit',
            'created_at' => '2026-06-11T10:28:37',
        ]));

        $response->assertOk();

        $this->assertDatabaseHas('scan_results', [
            'id' => $scan->id,
            'user_id' => $this->otherScanner->id,
            'sto_code_id' => $newSto->id,
            'sto_code' => 'STO2607',
            'barcode_raw' => 'RR2P051-00000835B|LOTEDIT|7',
            'barcode_material' => 'RR2P051-00000835B',
            'lot_number' => 'LOTEDIT',
            'qty' => 7,
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
            'keterangan' => 'Size Salah',
            'scan_source' => 'admin_edit',
            'created_at' => '2026-06-11 10:28:37',
        ]);

        $this->assertDatabaseHas('locations', [
            'user_id' => $this->otherScanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'Round Rack',
        ]);

        $this->assertDatabaseHas('scan_result_logs', [
            'scan_result_id' => $scan->id,
            'user_id' => $this->admin->id,
            'action' => 'updated',
            'field_name' => 'barcode_material',
            'new_value' => 'RR2P051-00000835B',
        ]);
    }

    public function test_admin_manual_scan_rejects_unknown_material(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/admin/api/scan-results', $this->adminPayload([
            'material_code' => 'ZZ',
            'material_name' => 'Unknown',
        ]));

        $response->assertStatus(422);
    }

    public function test_duplicate_scan_returns_warning(): void
    {
        $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload())->assertOk();

        $response = $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload());

        $response->assertStatus(409)
            ->assertJsonPath('duplicate', true);
    }

    public function test_valid_scan_store_directly_creates_row(): void
    {
        $response = $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload());

        $response->assertOk()
            ->assertJsonPath('data.barcode_material', 'RF1H059-00960099B')
            ->assertJsonPath('data.location', 'CT01');

        $this->assertDatabaseHas('scan_results', [
            'user_id' => $this->scanner->id,
            'barcode_material' => 'RF1H059-00960099B',
            'keterangan' => 'OK',
        ]);
    }

    public function test_scanner_recent_scan_only_shows_today_results(): void
    {
        $todayScan = $this->createScan($this->scanner, 'RF1H059-00960099B|TODAY|1');
        $yesterdayScan = $this->createScan($this->scanner, 'RF1H060-00970098B|YDAY|1');
        $yesterdayScan->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        $response = $this->actingAs($this->scanner)
            ->withSession([
                'scan_context' => [
                    'plant_id' => $this->plant->id,
                    'location_id' => $this->location->id,
                ],
            ])
            ->get('/scan/scanner');

        $response->assertOk()
            ->assertSee($todayScan->barcode_material)
            ->assertDontSee($yesterdayScan->barcode_material);
    }

    public function test_scanner_recent_scan_filters_by_selected_location(): void
    {
        $otherLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'CT02',
            'is_active' => true,
        ]);

        $selectedLocationScan = $this->createScan($this->scanner, 'RF1H059-00960001B|CT01|1');
        $otherLocationScan = $this->createScan($this->scanner, 'RF1H059-00960002B|CT02|1', [
            'location_id' => $otherLocation->id,
        ]);

        $response = $this->actingAs($this->scanner)
            ->withSession([
                'scan_context' => [
                    'plant_id' => $this->plant->id,
                    'location_id' => $this->location->id,
                ],
            ])
            ->get('/scan/scanner');

        $response->assertOk()
            ->assertSee($selectedLocationScan->barcode_material)
            ->assertDontSee($otherLocationScan->barcode_material)
            ->assertSee('id="counterToday">1</span>', false);
    }

    public function test_recent_scan_endpoint_filters_by_selected_location_context(): void
    {
        $otherLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'CT02',
            'is_active' => true,
        ]);

        $selectedLocationScan = $this->createScan($this->scanner, 'RF1H059-00960001B|CT01|1');
        $otherLocationScan = $this->createScan($this->scanner, 'RF1H059-00960002B|CT02|1', [
            'location_id' => $otherLocation->id,
        ]);

        $response = $this->actingAs($this->scanner)
            ->withSession([
                'scan_context' => [
                    'plant_id' => $this->plant->id,
                    'location_id' => $otherLocation->id,
                ],
            ])
            ->getJson('/api/scan/recent?page=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('total_today', 1)
            ->assertJsonFragment(['id' => $otherLocationScan->id])
            ->assertJsonMissing(['id' => $selectedLocationScan->id]);
    }

    public function test_scanner_recent_scan_displays_desc_number_and_action_meta_below_status(): void
    {
        $olderAt = now()->setTime(10, 28, 36);
        $newerAt = now()->setTime(10, 28, 37);

        $olderScan = $this->createScan($this->scanner, 'RF1H059-00960098B|OLDER|1');
        $olderScan->forceFill(['created_at' => $olderAt, 'updated_at' => $olderAt])->save();

        $newerScan = $this->createScan($this->scanner, 'RF1H059-00960099B|NEWER|1');
        $newerScan->forceFill(['created_at' => $newerAt, 'updated_at' => $newerAt])->save();

        $response = $this->actingAs($this->scanner)
            ->withSession([
                'scan_context' => [
                    'plant_id' => $this->plant->id,
                    'location_id' => $this->location->id,
                ],
            ])
            ->get('/scan/scanner');

        $response->assertOk()
            ->assertSee('<span class="recent-number">2</span>', false)
            ->assertSee('Auto Scan')
            ->assertSee('Select Barcode')
            ->assertSee('BarcodeDetector')
            ->assertSee('tap label barcode')
            ->assertSee('recent-action-meta', false)
            ->assertSee('CT01 &bull; ' . $newerAt->format('H:i:s'), false)
            ->assertSeeInOrder([
                $newerScan->barcode_material,
                'SKD11 - Flat - 59 x 96 x 99 - 1 pcs - ST2605',
                'OK',
                $newerAt->format('H:i:s'),
                $olderScan->barcode_material,
                $olderAt->format('H:i:s'),
            ])
            ->assertDontSee($newerAt->format('Y-m-d H:i:s'))
            ->assertDontSee('recent-time', false)
            ->assertDontSee('Lot ST2605 - ' . $newerAt->format('Y-m-d H:i:s'));
    }

    public function test_recent_scan_endpoint_paginates_today_results_for_current_user(): void
    {
        for ($i = 1; $i <= 55; $i++) {
            $this->createScan($this->scanner, sprintf('RF1H059-0096%04dB|LOT%03d|1', $i, $i));
        }

        $otherUserScan = $this->createScan($this->otherScanner, 'RF1H059-00969998B|OTHER|1');
        $yesterdayScan = $this->createScan($this->scanner, 'RF1H059-00969999B|YDAY|1');
        $yesterdayScan->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        $pageOne = $this->actingAs($this->scanner)->getJson('/api/scan/recent?page=1');

        $pageOne->assertOk()
            ->assertJsonCount(50, 'data')
            ->assertJsonPath('meta.page', 1)
            ->assertJsonPath('meta.per_page', 50)
            ->assertJsonPath('meta.total', 55)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonMissing(['id' => $otherUserScan->id])
            ->assertJsonMissing(['id' => $yesterdayScan->id]);

        $pageTwo = $this->actingAs($this->scanner)->getJson('/api/scan/recent?page=2');

        $pageTwo->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.per_page', 50)
            ->assertJsonPath('meta.total', 55)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_scan_history_filters_by_barcode_material(): void
    {
        $match = $this->createScan($this->scanner, 'RF1H059-00960001B|MATCH|1');
        $other = $this->createScan($this->scanner, 'RF1H059-00960002B|OTHER|1');

        $response = $this->actingAs($this->scanner)
            ->getJson('/api/scan/history?barcode_material=' . urlencode($match->barcode_material));

        $response->assertOk()
            ->assertJsonFragment(['id' => $match->id])
            ->assertJsonMissing(['id' => $other->id]);
    }

    public function test_scan_history_filters_by_material_code(): void
    {
        $match = $this->createScan($this->scanner, 'RR2P051-00000835B|MATCH|1', [
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);
        $other = $this->createScan($this->scanner, 'RF1H059-00960002B|OTHER|1');

        $response = $this->actingAs($this->scanner)
            ->getJson('/api/scan/history?material_code=2P');

        $response->assertOk()
            ->assertJsonFragment(['id' => $match->id])
            ->assertJsonMissing(['id' => $other->id]);
    }

    public function test_scan_history_filters_by_location_id(): void
    {
        $secondLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'CT02',
            'is_active' => true,
        ]);

        $match = $this->createScan($this->scanner, 'RF1H059-00960001B|MATCH|1', [
            'location_id' => $secondLocation->id,
        ]);
        $other = $this->createScan($this->scanner, 'RF1H059-00960002B|OTHER|1');

        $response = $this->actingAs($this->scanner)
            ->getJson('/api/scan/history?location_id=' . $secondLocation->id);

        $response->assertOk()
            ->assertJsonFragment(['id' => $match->id])
            ->assertJsonFragment(['location' => 'CT02'])
            ->assertJsonMissing(['id' => $other->id]);
    }

    public function test_scan_history_filters_by_plant_id(): void
    {
        $secondPlant = Plant::create(['name' => 'Deltamas', 'is_active' => true]);
        $secondLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $secondPlant->id,
            'name' => 'DM01',
            'is_active' => true,
        ]);

        $match = $this->createScan($this->scanner, 'RF1H059-00960001B|MATCH|1', [
            'plant_id' => $secondPlant->id,
            'location_id' => $secondLocation->id,
        ]);
        $other = $this->createScan($this->scanner, 'RF1H059-00960002B|OTHER|1');

        $response = $this->actingAs($this->scanner)
            ->getJson('/api/scan/history?plant_id=' . $secondPlant->id);

        $response->assertOk()
            ->assertJsonFragment(['id' => $match->id])
            ->assertJsonFragment(['plant' => 'Deltamas'])
            ->assertJsonMissing(['id' => $other->id]);
    }

    public function test_scan_history_filter_dropdown_options_are_owned_and_latest_ordered(): void
    {
        $oldAt = now()->subMinutes(10);
        $newAt = now();

        $olderScan = $this->createScan($this->scanner, 'RF1H059-00960001B|OLD|1');
        $olderScan->forceFill(['created_at' => $oldAt, 'updated_at' => $oldAt])->save();

        $newPlant = Plant::create(['name' => 'Deltamas', 'is_active' => true]);
        $newLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $newPlant->id,
            'name' => 'DM01',
            'is_active' => true,
        ]);
        $newerScan = $this->createScan($this->scanner, 'RR2P051-00000835B|NEW|1', [
            'plant_id' => $newPlant->id,
            'location_id' => $newLocation->id,
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);
        $newerScan->forceFill(['created_at' => $newAt, 'updated_at' => $newAt])->save();

        $otherPlant = Plant::create(['name' => 'Surabaya', 'is_active' => true]);
        $otherLocation = Location::create([
            'user_id' => $this->otherScanner->id,
            'plant_id' => $otherPlant->id,
            'name' => 'SB01',
            'is_active' => true,
        ]);
        $otherUserScan = $this->createScan($this->otherScanner, 'RF1H059-00969999B|OTHER|1', [
            'plant_id' => $otherPlant->id,
            'location_id' => $otherLocation->id,
        ]);

        $response = $this->actingAs($this->scanner)->get('/scan/history');

        $response->assertOk()
            ->assertSeeInOrder([$newerScan->barcode_material, $olderScan->barcode_material])
            ->assertSeeInOrder(['SKD61 (2P)', 'SKD11 (1H)'])
            ->assertSeeInOrder(['Deltamas', 'Cikarang'])
            ->assertSeeInOrder(['DM01', 'CT01'])
            ->assertDontSee($otherUserScan->barcode_material)
            ->assertDontSee('Surabaya');
    }

    public function test_duplicate_scan_can_be_saved_as_new_row_if_confirmed(): void
    {
        $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload())->assertOk();

        $response = $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload(['force_save' => true]));

        $response->assertOk();
        $this->assertSame(2, ScanResult::where('barcode_material', 'RF1H059-00960099B')->count());
    }

    public function test_admin_datatable_filters_by_sto(): void
    {
        $this->createScan($this->scanner);

        $response = $this->actingAs($this->admin)->getJson('/admin/api/scan-results?draw=1&start=0&length=10&sto_code=STO2606');

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1);
    }

    public function test_admin_scan_results_datatable_clamps_length_and_negative_start(): void
    {
        for ($i = 1; $i <= 105; $i++) {
            $this->createScan($this->scanner, sprintf('RF1H059-0096%04dB|LOT%03d|1', $i, $i));
        }

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/api/scan-results?draw=1&start=-25&length=1000');

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 105)
            ->assertJsonPath('recordsFiltered', 105)
            ->assertJsonCount(100, 'data')
            ->assertJsonPath('data.0.no', 105);
    }

    public function test_admin_can_open_material_double_page_and_scanner_cannot_access_endpoint(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/material-double')
            ->assertOk()
            ->assertSee('Material Double');

        $this->actingAs($this->scanner)
            ->getJson('/admin/api/material-double?draw=1&start=0&length=10')
            ->assertForbidden();
    }

    public function test_material_double_datatable_only_returns_duplicate_groups_per_qr_plant_location_and_clamps_length(): void
    {
        $secondLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'CT02',
            'is_active' => true,
        ]);

        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT001|1');
        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT002|1');
        $this->createScan($this->scanner, 'RF1H060-00960098B|SINGLE|1');
        $this->createScan($this->scanner, 'RR2P051-00000835B|LOT003|1', [
            'location_id' => $secondLocation->id,
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);
        $this->createScan($this->scanner, 'RR2P051-00000835B|LOT004|1', [
            'location_id' => $secondLocation->id,
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
            ->getJson('/admin/api/material-double?draw=1&start=-5&length=1000');

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'barcode_material' => 'RF1H059-00960099B',
                'location' => 'CT01',
                'duplicate_count' => 2,
            ])
            ->assertJsonFragment([
                'barcode_material' => 'RR2P051-00000835B',
                'location' => 'CT02',
                'duplicate_count' => 2,
            ])
            ->assertJsonMissing(['barcode_material' => 'RF1H060-00960098B']);
    }

    public function test_material_double_filters_affect_grouping(): void
    {
        $secondPlant = Plant::create(['name' => 'Deltamas', 'is_active' => true]);
        $secondLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $secondPlant->id,
            'name' => 'DM01',
            'is_active' => true,
        ]);

        $excluded = $this->createScan($this->scanner, 'RF1H059-00960099B|OLD001|1');
        $excluded->forceFill(['created_at' => '2026-06-10 09:00:00', 'updated_at' => '2026-06-10 09:00:00'])->save();
        $this->createScan($this->scanner, 'RF1H059-00960099B|OLD002|1')
            ->forceFill(['created_at' => '2026-06-10 09:05:00', 'updated_at' => '2026-06-10 09:05:00'])
            ->save();

        $matchOne = $this->createScan($this->scanner, 'RR2P051-00000835B|NEW001|1', [
            'plant_id' => $secondPlant->id,
            'location_id' => $secondLocation->id,
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);
        $matchOne->forceFill(['created_at' => '2026-06-11 10:00:00', 'updated_at' => '2026-06-11 10:00:00'])->save();
        $matchTwo = $this->createScan($this->scanner, 'RR2P051-00000835B|NEW002|1', [
            'plant_id' => $secondPlant->id,
            'location_id' => $secondLocation->id,
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);
        $matchTwo->forceFill(['created_at' => '2026-06-11 10:05:00', 'updated_at' => '2026-06-11 10:05:00'])->save();

        $query = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 25,
            'plant_id' => $secondPlant->id,
            'location_id' => $secondLocation->id,
            'material_code' => '2P',
            'date_from' => '2026-06-11',
            'date_to' => '2026-06-11',
        ]);

        $response = $this->actingAs($this->admin)->getJson("/admin/api/material-double?{$query}");

        $response->assertOk()
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['barcode_material' => 'RR2P051-00000835B'])
            ->assertJsonMissing(['barcode_material' => $excluded->barcode_material]);
    }

    public function test_material_double_valid_action_marks_group_but_keeps_it_visible(): void
    {
        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT001|1');
        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT002|1');

        $payload = [
            'barcode_material' => 'RF1H059-00960099B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
        ];

        $this->actingAs($this->admin)
            ->postJson('/admin/api/material-double/validate', $payload)
            ->assertOk()
            ->assertJsonPath('message', 'Duplicate QR berhasil diverifikasi.');

        $this->assertDatabaseHas('material_double_validations', [
            'barcode_material' => 'RF1H059-00960099B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'validated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/api/material-double?draw=1&start=0&length=10');

        $response->assertOk()
            ->assertJsonFragment([
                'barcode_material' => 'RF1H059-00960099B',
                'is_validated' => true,
            ]);
    }

    public function test_material_double_detail_follows_group_and_delete_selected_hard_deletes_with_audit(): void
    {
        $targetOne = $this->createScan($this->scanner, 'RF1H059-00960099B|LOT001|1');
        $targetTwo = $this->createScan($this->scanner, 'RF1H059-00960099B|LOT002|1');
        $otherLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'CT02',
            'is_active' => true,
        ]);
        $otherGroup = $this->createScan($this->scanner, 'RF1H059-00960099B|LOT003|1', [
            'location_id' => $otherLocation->id,
        ]);

        $query = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 25,
            'barcode_material' => 'RF1H059-00960099B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
        ]);

        $detail = $this->actingAs($this->admin)
            ->getJson("/admin/api/material-double/detail?{$query}");

        $detail->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonFragment(['id' => $targetOne->id])
            ->assertJsonFragment(['id' => $targetTwo->id])
            ->assertJsonMissing(['id' => $otherGroup->id]);

        $delete = $this->actingAs($this->admin)
            ->deleteJson('/admin/api/material-double/delete-selected', [
                'barcode_material' => 'RF1H059-00960099B',
                'plant_id' => $this->plant->id,
                'location_id' => $this->location->id,
                'ids' => [$targetOne->id],
            ]);

        $delete->assertOk()
            ->assertJsonPath('deleted_count', 1)
            ->assertJsonPath('message', 'Data duplicate terpilih berhasil dihapus dan duplicate QR berhasil diverifikasi.');

        $this->assertDatabaseMissing('scan_results', ['id' => $targetOne->id]);
        $this->assertDatabaseHas('scan_results', ['id' => $targetTwo->id]);
        $this->assertDatabaseHas('scan_results', ['id' => $otherGroup->id]);
        $this->assertDatabaseHas('material_double_validations', [
            'barcode_material' => 'RF1H059-00960099B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'validated_by' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('scan_result_logs', [
            'scan_result_id' => $targetOne->id,
            'user_id' => $this->admin->id,
            'action' => 'deleted',
        ]);
    }

    public function test_material_double_delete_selected_rejects_ids_outside_group(): void
    {
        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT001|1');
        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT002|1');
        $other = $this->createScan($this->scanner, 'RF1H060-00960098B|OTHER|1');

        $response = $this->actingAs($this->admin)
            ->deleteJson('/admin/api/material-double/delete-selected', [
                'barcode_material' => 'RF1H059-00960099B',
                'plant_id' => $this->plant->id,
                'location_id' => $this->location->id,
                'ids' => [$other->id],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('scan_results', ['id' => $other->id]);
    }

    public function test_material_double_scan_returns_duplicate_warning_then_force_save_creates_admin_scan(): void
    {
        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT001|1');
        $this->createScan($this->scanner, 'RF1H059-00960099B|LOT002|1');

        $payload = [
            'barcode_material' => 'RF1H059-00960099B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'qr' => 'RF1H059-00960099B|LOT003|1',
        ];

        $warning = $this->actingAs($this->admin)
            ->postJson('/admin/api/material-double/scan', $payload);

        $warning->assertStatus(409)
            ->assertJsonPath('duplicate', true);

        $created = $this->actingAs($this->admin)
            ->postJson('/admin/api/material-double/scan', array_merge($payload, ['force_save' => true]));

        $created->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_id', $this->admin->id)
            ->assertJsonPath('data.plant_id', $this->plant->id)
            ->assertJsonPath('data.location_id', $this->location->id)
            ->assertJsonPath('data.scan_source', 'material_double_scan');

        $scan = ScanResult::query()
            ->where('user_id', $this->admin->id)
            ->where('lot_number', 'LOT003')
            ->firstOrFail();

        $this->assertSame($this->stoCode->id, $scan->sto_code_id);
        $this->assertDatabaseHas('scan_result_logs', [
            'scan_result_id' => $scan->id,
            'user_id' => $this->admin->id,
            'action' => 'created',
        ]);
    }

    public function test_material_double_scan_rejects_invalid_unknown_or_mismatched_qr(): void
    {
        $basePayload = [
            'barcode_material' => 'RF1H059-00960099B',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
        ];

        $this->actingAs($this->admin)
            ->postJson('/admin/api/material-double/scan', array_merge($basePayload, [
                'qr' => 'invalid',
            ]))
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->actingAs($this->admin)
            ->postJson('/admin/api/material-double/scan', array_merge($basePayload, [
                'qr' => 'RF9Z059-00960099B|LOT001|1',
            ]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Kode material tidak ditemukan di Master Material.');

        $this->actingAs($this->admin)
            ->postJson('/admin/api/material-double/scan', array_merge($basePayload, [
                'qr' => 'RF1H060-00960098B|LOT001|1',
            ]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'QR yang discan tidak sesuai dengan barcode material pada baris Material Double.');
    }

    public function test_master_datatable_clamps_length_and_negative_start(): void
    {
        for ($i = 1; $i <= 105; $i++) {
            MasterMaterial::create([
                'material_code' => sprintf('T%03d', $i),
                'material_name' => sprintf('Test Material %03d', $i),
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/api/master-material?draw=1&start=-10&length=1000');

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 107)
            ->assertJsonPath('recordsFiltered', 107)
            ->assertJsonCount(100, 'data')
            ->assertJsonPath('data.0.no', 107);
    }

    public function test_login_endpoint_is_rate_limited(): void
    {
        config(['sto.rate_limits.login_per_minute' => 2]);
        RateLimiter::clear('login:limited-user|127.0.0.1');

        $this->post('/login', ['username' => 'limited-user', 'password' => 'wrong'])
            ->assertRedirect();
        $this->post('/login', ['username' => 'limited-user', 'password' => 'wrong'])
            ->assertRedirect();

        $this->post('/login', ['username' => 'limited-user', 'password' => 'wrong'])
            ->assertStatus(429);
    }

    public function test_scan_write_endpoint_is_rate_limited(): void
    {
        config(['sto.rate_limits.scan_write_per_minute' => 2]);
        RateLimiter::clear("scan-write:{$this->scanner->id}");

        $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload([
            'qr' => 'RF1H059-00960001B|LOT001|1',
        ]))->assertOk();

        $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload([
            'qr' => 'RF1H059-00960002B|LOT002|1',
        ]))->assertOk();

        $this->actingAs($this->scanner)->postJson('/api/scan/store', $this->payload([
            'qr' => 'RF1H059-00960003B|LOT003|1',
        ]))->assertStatus(429);
    }

    public function test_export_queue_endpoint_is_rate_limited(): void
    {
        Queue::fake();
        config(['sto.rate_limits.export_per_minute' => 1]);
        RateLimiter::clear("export:{$this->admin->id}");

        $this->actingAs($this->admin)
            ->postJson('/admin/export/scan-results/excel')
            ->assertStatus(202);

        $this->actingAs($this->admin)
            ->postJson('/admin/export/scan-results/excel')
            ->assertStatus(429);
    }

    public function test_datatable_endpoint_is_rate_limited(): void
    {
        config(['sto.rate_limits.datatable_per_minute' => 1]);
        RateLimiter::clear("datatable:{$this->admin->id}");

        $this->actingAs($this->admin)
            ->getJson('/admin/api/scan-results?draw=1&start=0&length=10')
            ->assertOk();

        $this->actingAs($this->admin)
            ->getJson('/admin/api/scan-results?draw=1&start=0&length=10')
            ->assertStatus(429);
    }

    public function test_admin_dashboard_does_not_render_latest_scan_rows_in_blade(): void
    {
        $scan = $this->createScan($this->scanner, 'RF1H059-00960055B|LOT055|1');

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertOk()
            ->assertSee('dashboardDataTable')
            ->assertDontSee($scan->barcode_material);
    }

    public function test_admin_dashboard_latest_scan_endpoint_is_server_side_and_clamped_to_fifty_rows(): void
    {
        for ($i = 1; $i <= 55; $i++) {
            $this->createScan($this->scanner, sprintf('RF1H059-0096%04dB|LOT%03d|1', $i, $i));
        }

        $pageOne = $this->actingAs($this->admin)->getJson('/admin/api/dashboard/latest-scan?draw=3&start=0&length=100');

        $pageOne->assertOk()
            ->assertJsonPath('draw', 3)
            ->assertJsonPath('recordsTotal', 55)
            ->assertJsonPath('recordsFiltered', 55)
            ->assertJsonCount(50, 'data')
            ->assertJsonFragment(['barcode_material' => 'RF1H059-00960055B'])
            ->assertJsonMissing(['barcode_material' => 'RF1H059-00960005B']);

        $pageTwo = $this->actingAs($this->admin)->getJson('/admin/api/dashboard/latest-scan?draw=4&start=50&length=100');

        $pageTwo->assertOk()
            ->assertJsonPath('draw', 4)
            ->assertJsonCount(5, 'data')
            ->assertJsonFragment(['barcode_material' => 'RF1H059-00960005B'])
            ->assertJsonMissing(['barcode_material' => 'RF1H059-00960055B']);
    }

    public function test_admin_dashboard_latest_scan_endpoint_applies_dashboard_filters(): void
    {
        $otherPlant = Plant::create(['name' => 'Deltamas', 'is_active' => true]);
        $otherLocation = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $otherPlant->id,
            'name' => 'DM01',
            'is_active' => true,
        ]);

        $older = $this->createScan($this->scanner, 'RF1H059-00960001B|LOT001|1');
        $older->forceFill([
            'created_at' => '2026-06-10 10:00:00',
            'updated_at' => '2026-06-10 10:00:00',
        ])->save();

        $included = $this->createScan($this->scanner, 'RF1H059-00960002B|LOT002|1', [
            'plant_id' => $otherPlant->id,
            'location_id' => $otherLocation->id,
        ]);
        $included->forceFill([
            'created_at' => '2026-06-11 10:00:00',
            'updated_at' => '2026-06-11 10:00:00',
        ])->save();

        $newer = $this->createScan($this->scanner, 'RF1H059-00960003B|LOT003|1', [
            'plant_id' => $otherPlant->id,
            'location_id' => $otherLocation->id,
        ]);
        $newer->forceFill([
            'created_at' => '2026-06-12 10:00:00',
            'updated_at' => '2026-06-12 10:00:00',
        ])->save();

        $query = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 50,
            'plant_id' => $otherPlant->id,
            'date_from' => '2026-06-11',
            'date_to' => '2026-06-11',
        ]);

        $response = $this->actingAs($this->admin)->getJson("/admin/api/dashboard/latest-scan?{$query}");

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['barcode_material' => $included->barcode_material])
            ->assertJsonMissing(['barcode_material' => 'RF1H059-00960001B'])
            ->assertJsonMissing(['barcode_material' => 'RF1H059-00960003B']);
    }

    public function test_admin_dashboard_latest_scan_endpoint_searches_server_side(): void
    {
        $this->createScan($this->scanner, 'RF1H059-00960001B|LOT001|1');
        $matched = $this->createScan($this->scanner, 'RR2P051-00000835B|LOT002|1', [
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);

        $query = http_build_query([
            'draw' => 1,
            'start' => 0,
            'length' => 50,
            'search' => ['value' => 'SKD61'],
        ]);

        $response = $this->actingAs($this->admin)->getJson("/admin/api/dashboard/latest-scan?{$query}");

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['barcode_material' => $matched->barcode_material])
            ->assertJsonMissing(['barcode_material' => 'RF1H059-00960001B']);
    }

    public function test_recent_scan_serializer_formats_flat_and_round_dimensions(): void
    {
        $flat = $this->createScan($this->scanner);
        $round = $this->createScan($this->scanner, 'RR2P051-00000835B|ST2605|1', [
            'material_code' => '2P',
            'material_name' => 'SKD61',
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'thickness' => null,
            'width' => null,
            'diameter' => 51,
            'length' => 835,
        ]);

        $service = app(ScanService::class);

        $this->assertSame('59 x 96 x 99', $service->serializeScan($flat)['display_size']);
        $this->assertSame('⌀51 x 835', $service->serializeScan($round)['display_size']);
        $this->assertStringContainsString('SKD61 - Round - ⌀51 x 835 - Lot ST2605 - ', $service->serializeScan($round)['recent_detail']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $service->serializeScan($round)['recent_detail']);
    }

    public function test_master_sto_activation_deactivates_others(): void
    {
        $newSto = StoCode::create(['code' => 'STO2607', 'is_active' => false]);

        $response = $this->actingAs($this->admin)->postJson("/admin/api/master-sto/{$newSto->id}/activate");

        $response->assertOk();
        $this->assertTrue($newSto->fresh()->is_active);
        $this->assertFalse($this->stoCode->fresh()->is_active);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'qr' => 'RF1H059-00960099B|ST2605|1',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'scan_source' => 'manual',
        ], $override);
    }

    private function adminPayload(array $override = []): array
    {
        return array_merge([
            'user_id' => $this->scanner->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_name' => 'CT01',
            'barcode_raw' => 'RF1H059-00960099B|ST2605|1',
            'barcode_material' => 'RF1H059-00960099B',
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
            'scan_source' => 'admin',
            'created_at' => '2026-06-11T10:28:37',
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
}
