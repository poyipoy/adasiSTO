<?php

namespace Tests\Unit;

use App\Models\MasterMaterial;
use App\Services\BarcodeParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeParserServiceTest extends TestCase
{
    use RefreshDatabase;

    private BarcodeParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();

        MasterMaterial::create(['material_code' => '1H', 'material_name' => 'SKD11', 'is_active' => true]);
        MasterMaterial::create(['material_code' => '2P', 'material_name' => 'SKD61', 'is_active' => true]);
        $this->parser = app(BarcodeParserService::class);
    }

    public function test_parse_rf_valid(): void
    {
        $result = $this->parser->parse('RF1H059-00960099B|ST2605|1');

        $this->assertTrue($result['valid']);
        $this->assertSame('Flat', $result['shape_name']);
        $this->assertSame('1H', $result['material_code']);
        $this->assertSame(59, $result['thickness']);
        $this->assertSame(96, $result['width']);
        $this->assertSame(99, $result['length']);
        $this->assertSame('ST2605', $result['lot_number']);
        $this->assertSame(1, $result['qty']);
    }

    public function test_parse_rr_valid(): void
    {
        $result = $this->parser->parse('RR2P051-00000835B|ST2605|1');

        $this->assertTrue($result['valid']);
        $this->assertSame('Round', $result['shape_name']);
        $this->assertSame('2P', $result['material_code']);
        $this->assertNull($result['thickness']);
        $this->assertNull($result['width']);
        $this->assertSame(51, $result['diameter']);
        $this->assertSame(835, $result['length']);
    }

    public function test_reject_unknown_shape(): void
    {
        $result = $this->parser->parse('XX1H059-00960099B|ST2605|1');

        $this->assertFalse($result['valid']);
    }

    public function test_reject_unknown_material(): void
    {
        $result = $this->parser->parse('RFZZ059-00960099B|ST2605|1');

        $this->assertFalse($result['valid']);
        $this->assertSame('Kode material tidak ditemukan di Master Material.', $result['message']);
    }

    public function test_parse_missing_or_empty_lot_defaults_to_dash(): void
    {
        $formats = [
            'RF1H059-00960099B || 1',
            'RF1H059-00960099B | | 1',
            'RF1H059-00960099B |  | 1',
            'RF1H059-00960099B | - | 1',
            'RF1H059-00960099B||1',
        ];

        foreach ($formats as $qr) {
            $result = $this->parser->parse($qr);
            $this->assertTrue($result['valid'], "QR [$qr] seharusnya valid.");
            $this->assertSame('-', $result['lot_number'], "QR [$qr] lot_number seharusnya '-'");
        }
    }

    public function test_reject_missing_qty(): void
    {
        $result = $this->parser->parse('RF1H059-00960099B|ST2605');

        $this->assertFalse($result['valid']);
    }

    public function test_reject_invalid_qty(): void
    {
        $result = $this->parser->parse('RF1H059-00960099B|ST2605|ABC');

        $this->assertFalse($result['valid']);
        $this->assertSame('Qty tidak valid.', $result['message']);
    }

    public function test_trim_whitespace_around_qr_parts(): void
    {
        $result = $this->parser->parse('RF1H059-00960099B | ST2605 | 1');

        $this->assertTrue($result['valid']);
        $this->assertSame('ST2605', $result['lot_number']);
    }
}
