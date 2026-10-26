<?php

namespace Tests\Feature;

use App\Models\BarcodeRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\StoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup user scanner and admin
        $this->scanner = User::factory()->create(['role' => 'scanner']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->stoCode = StoCode::create(['code' => 'STO-BARCODE-TEST', 'is_active' => true]);

        // Setup base data
        $this->plant = Plant::factory()->create();
        $this->location = Location::factory()->create(['plant_id' => $this->plant->id]);
        $this->material = MasterMaterial::factory()->create();
    }

    public function test_admin_cannot_access_barcode_request_page()
    {
        $response = $this->actingAs($this->admin)->get(route('scan.barcode-request'));
        $response->assertForbidden();
    }

    public function test_scanner_can_access_barcode_request_page()
    {
        $response = $this->actingAs($this->scanner)->get(route('scan.barcode-request'));
        $response->assertStatus(200);
    }

    public function test_scanner_can_create_rf_barcode_request()
    {
        $payload = [
            'material_code' => $this->material->material_code,
            'shape_code' => 'RF',
            'thickness' => 10,
            'width' => 20,
            'length' => 100,
            'lot_number' => 'LOT-RF-001',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'print_count' => 50,
        ];

        $response = $this->actingAs($this->scanner)->postJson(route('api.barcode-request.store'), $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('barcode_requests', [
            'user_id' => $this->scanner->id,
            'shape_code' => 'RF',
            'thickness' => 10,
            'width' => 20,
            'length' => 100,
            'diameter' => null,
            'status' => 'pending',
            'qty' => 1,
            'print_count' => 50,
        ]);
    }

    public function test_scanner_can_create_rr_barcode_request()
    {
        $payload = [
            'material_code' => $this->material->material_code,
            'shape_code' => 'RR',
            'diameter' => 30,
            'length' => 150,
            'lot_number' => 'LOT-RR-001',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'print_count' => 2,
        ];

        $response = $this->actingAs($this->scanner)->postJson(route('api.barcode-request.store'), $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('barcode_requests', [
            'user_id' => $this->scanner->id,
            'shape_code' => 'RR',
            'thickness' => null,
            'width' => null,
            'diameter' => 30,
            'length' => 150,
            'status' => 'pending',
            'qty' => 1,
            'print_count' => 2,
        ]);
    }

    public function test_cannot_create_duplicate_pending_request()
    {
        $payload = [
            'material_code' => $this->material->material_code,
            'shape_code' => 'RF',
            'thickness' => 10,
            'width' => 20,
            'length' => 100,
            'lot_number' => 'LOT-DUP',
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'print_count' => 1,
        ];

        // First request should succeed
        $this->actingAs($this->scanner)->postJson(route('api.barcode-request.store'), $payload)->assertStatus(201);

        // Second duplicate request should fail
        $response = $this->actingAs($this->scanner)->postJson(route('api.barcode-request.store'), $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['duplicate']);
    }

    public function test_scanner_can_cancel_own_pending_request()
    {
        $request = BarcodeRequest::create([
            'user_id' => $this->scanner->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'material_code' => $this->material->material_code,
            'material_name' => $this->material->material_name,
            'shape_code' => 'RF',
            'shape_name' => 'FLAT',
            'thickness' => 10,
            'width' => 20,
            'length' => 100,
            'lot_number' => 'LOT-CANCEL',
            'qty' => 1,
            'print_count' => 1,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/barcode-request/{$request->id}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('barcode_requests', ['id' => $request->id]);
    }

    public function test_scanner_cannot_cancel_others_request()
    {
        $otherScanner = User::factory()->create(['role' => 'scanner']);

        $request = BarcodeRequest::create([
            'user_id' => $otherScanner->id,
            'sto_code_id' => $this->stoCode->id,
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'material_code' => $this->material->material_code,
            'material_name' => $this->material->material_name,
            'shape_code' => 'RF',
            'shape_name' => 'FLAT',
            'thickness' => 10,
            'width' => 20,
            'length' => 100,
            'lot_number' => 'LOT-OTHER',
            'qty' => 1,
            'print_count' => 1,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/barcode-request/{$request->id}");
        $response->assertForbidden();
    }
}
