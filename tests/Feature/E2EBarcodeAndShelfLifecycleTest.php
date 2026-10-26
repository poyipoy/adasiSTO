<?php

namespace Tests\Feature;

use App\Models\BarcodeRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\RackConfirmation;
use App\Models\StoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class E2EBarcodeAndShelfLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $scanner;

    private User $admin;

    private User $validator;

    private Plant $plant;

    private Location $location;

    private MasterMaterial $material;

    private StoCode $stoCode;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scanner = User::factory()->create(['role' => 'scanner', 'is_active' => true]);
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->validator = User::factory()->create(['role' => 'scanner', 'is_validator' => true, 'is_active' => true]);
        $this->stoCode = StoCode::create(['code' => 'STO2026', 'is_active' => true]);
        $this->plant = Plant::create(['name' => 'Cikarang Plant', 'is_active' => true]);
        $this->location = Location::create([
            'user_id' => $this->scanner->id,
            'plant_id' => $this->plant->id,
            'name' => 'RAK-A-01',
            'is_active' => true,
            'is_confirmed' => false,
            'created_by_user_id' => $this->scanner->id,
        ]);
        $this->material = MasterMaterial::create([
            'material_code' => '1H',
            'material_name' => 'SKD11 Steel Plate',
            'is_active' => true,
        ]);
    }

    public function test_full_lifecycle_barcode_request_to_generate_and_rack_confirmation(): void
    {
        // 1. Scanner makes a new barcode request
        $payload = [
            'material_code' => $this->material->material_code,
            'shape_code' => 'RF',
            'thickness' => 12,
            'width' => 300,
            'length' => 1200,
            'lot_number' => 'LOT-RF-E2E-001',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'print_count' => 5,
        ];

        $response = $this->actingAs($this->scanner)->postJson(route('api.barcode-request.store'), $payload);
        $response->assertStatus(201);

        $barcodeRequest = BarcodeRequest::where('lot_number', 'LOT-RF-E2E-001')->firstOrFail();
        $this->assertEquals('pending', $barcodeRequest->status);

        // 2. Admin verifies sidebar badges (remember cache)
        $badges = Cache::remember('sidebar_badges', 15, function () {
            return [
                'pending_barcode' => BarcodeRequest::where('status', 'pending')->count(),
                'unconfirmed_rak' => Location::where('is_confirmed', false)->count(),
            ];
        });
        $this->assertEquals(1, $badges['pending_barcode']);
        $this->assertEquals(1, $badges['unconfirmed_rak']);

        // 3. Admin generates/approves the barcode request
        $generateResponse = $this->actingAs($this->admin)->postJson(
            route('admin.api.generate-barcode.generate', $barcodeRequest->id),
            []
        );
        $generateResponse->assertStatus(201);

        $barcodeRequest->refresh();
        $this->assertEquals('approved', $barcodeRequest->status);
        $this->assertEquals(1, $barcodeRequest->qty);
        $this->assertEquals(5, $barcodeRequest->print_count);
        $this->assertStringEndsWith('|LOT-RF-E2E-001|1', $barcodeRequest->full_barcode);
        $this->assertNotEmpty($barcodeRequest->generated_barcode_material);
        $this->assertNull(Cache::get('sidebar_badges'), 'Cache sidebar_badges should be cleared on generate');

        // 4. Scanner checks dimension suggestions based on history
        $suggestionResponse = $this->actingAs($this->scanner)->getJson(
            route('api.barcode-request.suggestions', ['material_code' => '1H'])
        );
        $suggestionResponse->assertStatus(200);
        $suggestionResponse->assertJsonPath('success', true);
        $suggestionResponse->assertJsonPath('suggestion.shape_code', 'RF');
        $suggestionResponse->assertJsonPath('suggestion.thickness', 12);

        // 5. Scanner, validator, then admin complete the sequential rack workflow.
        $this->actingAs($this->scanner)->postJson(
            route('admin.api.rack-confirmation.finish', $this->location),
            ['note' => 'Penghitungan rack selesai']
        )->assertOk();

        $this->actingAs($this->validator)->postJson(
            route('admin.api.rack-confirmation.validate', $this->location),
            ['note' => 'Data rack sudah divalidasi']
        )->assertOk();

        $confirmResponse = $this->actingAs($this->admin)->postJson(
            route('admin.api.rack-confirmation.confirm', $this->location->id),
            ['note' => 'Rak fisik sudah diverifikasi admin']
        );
        $confirmResponse->assertStatus(200);

        $this->location->refresh();
        $this->assertTrue($this->location->is_confirmed);
        $this->assertEquals($this->admin->id, $this->location->confirmed_by_user_id);
        $this->assertDatabaseHas('rack_confirmations', [
            'sto_code_id' => $this->stoCode->id,
            'location_id' => $this->location->id,
            'status' => RackConfirmation::STATUS_CONFIRMED,
        ]);
        $this->assertDatabaseCount('rack_confirmation_histories', 3);
        $this->assertNull(Cache::get('sidebar_badges'), 'Cache sidebar_badges should be cleared on rack confirmation');
    }

    public function test_batch_generate_workflow(): void
    {
        $req1 = BarcodeRequest::create([
            'user_id' => $this->scanner->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'material_code' => '1H',
            'material_name' => 'SKD11 Steel Plate',
            'shape_code' => 'RF',
            'shape_name' => 'FLAT',
            'thickness' => 10,
            'width' => 100,
            'length' => 500,
            'lot_number' => 'LOT-BATCH-1',
            'qty' => 1,
            'print_count' => 4,
            'status' => 'pending',
        ]);

        $req2 = BarcodeRequest::create([
            'user_id' => $this->scanner->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'material_code' => '1H',
            'material_name' => 'SKD11 Steel Plate',
            'shape_code' => 'RR',
            'shape_name' => 'ROUND',
            'diameter' => 50,
            'length' => 1000,
            'lot_number' => 'LOT-BATCH-2',
            'qty' => 1,
            'print_count' => 2,
            'status' => 'pending',
        ]);

        // Batch Generate
        $batchGenPayload = ['ids' => [$req1->id, $req2->id]];

        $resGen = $this->actingAs($this->admin)->postJson(route('admin.api.generate-barcode.batch-generate'), $batchGenPayload);
        $resGen->assertStatus(200);
        $resGen->assertJsonPath('success', true);
        $resGen->assertJsonPath('processed', 2);

        $req1->refresh();
        $req2->refresh();
        $this->assertEquals('approved', $req1->status);
        $this->assertEquals(1, $req1->qty);
        $this->assertEquals(4, $req1->print_count);
        $this->assertEquals('approved', $req2->status);
        $this->assertEquals(1, $req2->qty);
        $this->assertEquals(2, $req2->print_count);

        $this->assertDatabaseCount('activity_logs', 2);
    }

    public function test_rack_confirmation_export_excel_and_csv(): void
    {
        $resExcel = $this->actingAs($this->admin)->get(route('admin.rack-confirmation.export', ['format' => 'excel']));
        $resExcel->assertStatus(200);
        $resExcel->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $resCsv = $this->actingAs($this->admin)->get(route('admin.rack-confirmation.export', ['format' => 'csv']));
        $resCsv->assertStatus(200);
        $resCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
