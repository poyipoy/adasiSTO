<?php

namespace Database\Seeders;

use App\Models\MasterMaterial;
use Illuminate\Database\Seeder;

class MasterMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $materials = [
            '1A' => 'DC11',
            '1B' => 'DC53',
            '1C' => 'DCMX',
            '1D' => 'GO40F',
            '1E' => 'GO5',
            '1F' => 'GOA',
            '1G' => 'KNS',
            '1H' => 'SKD11',
            '1I' => 'YK30',
            '1J' => 'YK4',
            '2A' => 'CND',
            '2B' => 'DH2F',
            '2C' => 'DH31',
            '2D' => 'DH31EX',
            '2E' => 'DH31S',
            '2F' => 'DH32',
            '2G' => 'DH62',
            '2H' => 'DHA',
            '2I' => 'DHA1',
            '2J' => 'DHA1E',
            '2K' => 'DHAT',
            '2L' => 'DHAW',
            '2M' => 'F3RV',
            '2N' => 'GFA',
            '2O' => 'RT90',
            '2P' => 'SKD61',
            '2Q' => 'SKT4',
            '2R' => 'SUJ',
            '2S' => 'DHA1ES',
            '3A' => 'DRM1',
            '3B' => 'DRM2',
            '3C' => 'DRM3',
            '3D' => 'MH51',
            '3E' => 'MH55',
            '3F' => 'MH85',
            '3G' => 'SKH9',
            '3H' => 'YXM1',
            '4A' => '1.2316',
            '4B' => '1.2738',
            '4C' => 'HP4MA',
            '4D' => 'NAK55',
            '4E' => 'NAK80',
            '4F' => 'P20',
            '4G' => 'PAC5000',
            '4H' => 'PAK90',
            '4I' => 'PDS5M',
            '4J' => 'PX4',
            '4K' => 'PX5',
            '4L' => 'PXA30',
            '4M' => 'S-STAR',
            '4N' => 'S-STAR Annealed',
            '4O' => 'F3RV',
            '5A' => 'S35C',
            '5B' => 'S45C',
            '5C' => 'S55C',
            '5D' => 'SCM415',
            '5E' => 'SCM420',
            '5F' => 'SCM420H',
            '5G' => 'SCM440',
            '5H' => 'SCM440H',
            '5I' => 'SCM440Q',
            '5J' => 'SNCM439',
            '5K' => 'SNCM447',
            '5L' => 'SS400',
            '5M' => 'ST52',
            '5N' => 'DX50',
            '5O' => 'S50C',
            '5P' => 'SNCM439Q',
            '6A' => 'BECU',
            '6B' => 'Aluminium 1100',
            '6C' => 'Aluminium 6061',
            '6D' => 'Aluminium 7075',
            '6E' => 'Aluminium 5052',
            '7A' => 'SUS 304',
            '7B' => 'SUJ2',
        ];

        foreach ($materials as $code => $name) {
            MasterMaterial::updateOrCreate(
                ['material_code' => $code],
                ['material_name' => $name, 'is_active' => true]
            );
        }

        MasterMaterial::whereNotIn('material_code', array_keys($materials))
            ->update(['is_active' => false]);
    }
}
