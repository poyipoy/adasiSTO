<?php

namespace App\Models;

use App\Services\ShapeFormatter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class BarcodeDeleteHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'original_scan_result_id', 'sto_code_id', 'sto_code',
        'deleted_by_user_id', 'deleted_by_name', 'original_user_id', 'original_user_name',
        'plant_id', 'plant_name', 'location_id', 'location_name',
        'barcode_raw', 'barcode_material', 'material_code', 'material_name',
        'shape_code', 'shape_name', 'thickness', 'width', 'diameter', 'length',
        'lot_number', 'qty', 'keterangan', 'scan_source', 'deletion_source',
        'deletion_reason', 'snapshot', 'original_scanned_at', 'deleted_at',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Barcode delete history bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Barcode delete history bersifat append-only.'));
    }

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'original_scanned_at' => 'datetime',
            'deleted_at' => 'datetime',
            'thickness' => 'integer',
            'width' => 'integer',
            'diameter' => 'integer',
            'length' => 'integer',
            'qty' => 'integer',
        ];
    }

    protected function shapeName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ShapeFormatter::name($this->shape_code, $value),
        );
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    public function originalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }
}
