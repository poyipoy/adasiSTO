<?php

namespace App\Services;

final class ShapeFormatter
{
    private const NAMES = [
        'RF' => 'FLAT',
        'RR' => 'ROUND',
        'RH' => 'HOLLOW',
    ];

    public static function name(?string $shapeCode, ?string $fallback = null): string
    {
        $code = strtoupper(trim((string) $shapeCode));

        return self::NAMES[$code] ?? strtoupper(trim((string) $fallback));
    }

    public static function supportedCodes(): array
    {
        return array_keys(self::NAMES);
    }
}
