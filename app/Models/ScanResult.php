<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScanResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sto_code_id',
        'plant_id',
        'location_id',
        'sto_code',
        'barcode_raw',
        'barcode_material',
        'lot_number',
        'qty',
        'material_code',
        'material_name',
        'shape_code',
        'shape_name',
        'thickness',
        'width',
        'diameter',
        'length',
        'keterangan',
        'scan_source',
    ];

    protected function casts(): array
    {
        return [
            'thickness' => 'integer',
            'width' => 'integer',
            'diameter' => 'integer',
            'length' => 'integer',
            'qty' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stoCode(): BelongsTo
    {
        return $this->belongsTo(StoCode::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ScanResultLog::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function getSizeAttribute(): string
    {
        if ($this->shape_code === 'RF') {
            return $this->formatDimension($this->thickness)
                . ' x ' . $this->formatDimension($this->width)
                . ' x ' . $this->formatDimension($this->length);
        }

        if ($this->shape_code === 'RR') {
            return '⌀' . $this->formatDimension($this->diameter)
                . ' x ' . $this->formatDimension($this->length);
        }

        return '-';
    }

    public function getRecentDetailAttribute(): string
    {
        return "{$this->material_name} - {$this->shape_name} - {$this->size} - Lot {$this->lot_number} - {$this->created_at?->format('Y-m-d H:i:s')}";
    }

    private function formatDimension(?int $value): string
    {
        return $value === null ? '-' : (string) $value;
    }
}
