<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plant;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\BarcodeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'material_code' => $this->material->material_code,
            'shape_code' => 'RF',
            'thickness' => 10,
            'width' => 20,
            'length' => 100,
            'lot_number' => 'LOT-CANCEL',
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
            'plant_id' => $this->plant->id,
            'location_id' => $this->location->id,
            'material_code' => $this->material->material_code,
            'shape_code' => 'RF',
            'thickness' => 10,
            'width' => 20,
            'length' => 100,
            'lot_number' => 'LOT-OTHER',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->scanner)->deleteJson("/api/barcode-request/{$request->id}");
        $response->assertForbidden();
    }
}
