<?php

namespace Tests\Unit;

use App\Models\MasterMaterial;
use App\Services\BarcodeGeneratorService;
use App\Services\BarcodeParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Round-trip test: BarcodeGeneratorService::build() output must parse
 * cleanly via BarcodeParserService::parse() and return the original values.
 *
 * Per MISSION-02 acceptance criteria:
 * "BarcodeGeneratorService::build() menghasilkan string yang lolos parsing ulang
 *  oleh BarcodeParserService::parse() dengan data yang sama — dibuktikan lewat test otomatis."
 */
class BarcodeGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private BarcodeGeneratorService $generator;
    private BarcodeParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new BarcodeGeneratorService();
        $this->parser    = new BarcodeParserService();

        // Seed a master material for tests
        MasterMaterial::create([
            'material_code' => 'A1',
            'material_name' => 'Test Material RF',
            'is_active'     => true,
        ]);
        MasterMaterial::create([
            'material_code' => 'B2',
            'material_name' => 'Test Material RR',
            'is_active'     => true,
        ]);
    }

    /** @test */
    public function rf_barcode_round_trips_correctly(): void
    {
        $input = [
            'shape_code'    => 'RF',
            'material_code' => 'A1',
            'thickness'     => 6,
            'width'         => 50,
            'diameter'      => null,
            'length'        => 3000,
        ];

        $built = $this->generator->build($input);

        $this->assertTrue($built['valid'], 'Generator should succeed for valid RF data. Errors: ' . implode(', ', $built['errors']));
        $this->assertNotNull($built['barcode_material']);

        // Assemble full barcode string with lot + qty for parser
        $fullBarcode = $built['barcode_material'] . ' | LOT001 | 5';
        $parsed = $this->parser->parse($fullBarcode);

        $this->assertTrue($parsed['valid'], 'Parser should accept generator output. Message: ' . ($parsed['message'] ?? ''));
        $this->assertEquals('RF', $parsed['shape_code']);
        $this->assertEquals('A1', $parsed['material_code']);
        $this->assertEquals(6, $parsed['thickness']);
        $this->assertEquals(50, $parsed['width']);
        $this->assertEquals(3000, $parsed['length']);
        $this->assertNull($parsed['diameter']);
        $this->assertEquals('LOT001', $parsed['lot_number']);
        $this->assertEquals(5, $parsed['qty']);
    }

    /** @test */
    public function rr_barcode_round_trips_correctly(): void
    {
        $input = [
            'shape_code'    => 'RR',
            'material_code' => 'B2',
            'thickness'     => null,
            'width'         => null,
            'diameter'      => 25,
            'length'        => 6000,
        ];

        $built = $this->generator->build($input);

        $this->assertTrue($built['valid'], 'Generator should succeed for valid RR data. Errors: ' . implode(', ', $built['errors']));
        $this->assertNotNull($built['barcode_material']);

        $fullBarcode = $built['barcode_material'] . ' | LOTXYZ | 10';
        $parsed = $this->parser->parse($fullBarcode);

        $this->assertTrue($parsed['valid'], 'Parser should accept generator output for RR.');
        $this->assertEquals('RR', $parsed['shape_code']);
        $this->assertEquals('B2', $parsed['material_code']);
        $this->assertEquals(25, $parsed['diameter']);
        $this->assertEquals(6000, $parsed['length']);
        $this->assertNull($parsed['thickness']);
        $this->assertNull($parsed['width']);
    }

    /** @test */
    public function rf_barcode_has_correct_format(): void
    {
        $input = [
            'shape_code'    => 'RF',
            'material_code' => 'A1',
            'thickness'     => 12,
            'width'         => 100,
            'diameter'      => null,
            'length'        => 9000,
        ];

        $built = $this->generator->build($input);
        $this->assertTrue($built['valid']);

        // Expected: RFA1012-10009000B
        // ShapeCode=RF, MaterialCode=A1, Primary=012(3digit), -, width=0100(4digit)+length=9000(4digit), CheckLetter=B
        $this->assertMatchesRegularExpression(
            '/^(RF|RR)[A-Z0-9]{2}\d{3}-\d{8}[A-Z]$/',
            $built['barcode_material'],
            'Barcode format must match pattern'
        );
    }

    /** @test */
    public function rr_barcode_has_zero_width_in_secondary(): void
    {
        $input = [
            'shape_code'    => 'RR',
            'material_code' => 'B2',
            'thickness'     => null,
            'width'         => null,
            'diameter'      => 50,
            'length'        => 4000,
        ];

        $built = $this->generator->build($input);
        $this->assertTrue($built['valid']);

        // Secondary must be: 0000 (width=0) + 4000 (length)
        $this->assertStringContainsString('-00004000', $built['barcode_material']);
    }

    /** @test */
    public function generator_rejects_inactive_material(): void
    {
        MasterMaterial::create([
            'material_code' => 'ZZ',
            'material_name' => 'Inactive Material',
            'is_active'     => false,
        ]);

        $input = [
            'shape_code'    => 'RF',
            'material_code' => 'ZZ',
            'thickness'     => 5,
            'width'         => 50,
            'diameter'      => null,
            'length'        => 3000,
        ];

        $result = $this->generator->build($input);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    /** @test */
    public function generator_rejects_zero_thickness_for_rf(): void
    {
        $input = [
            'shape_code'    => 'RF',
            'material_code' => 'A1',
            'thickness'     => 0,
            'width'         => 50,
            'diameter'      => null,
            'length'        => 3000,
        ];

        $result = $this->generator->build($input);
        $this->assertFalse($result['valid']);
    }

    /** @test */
    public function generator_rejects_zero_diameter_for_rr(): void
    {
        $input = [
            'shape_code'    => 'RR',
            'material_code' => 'B2',
            'thickness'     => null,
            'width'         => null,
            'diameter'      => 0,
            'length'        => 3000,
        ];

        $result = $this->generator->build($input);
        $this->assertFalse($result['valid']);
    }
}
