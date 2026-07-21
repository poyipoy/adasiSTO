<?php

namespace App\Models;

use App\Services\ActiveStoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScanResult extends Model
{
    use HasFactory;

    /**
     * Global Scope: Otomatis memfilter seluruh query ScanResult
     * agar hanya menampilkan data milik periode STO yang sedang aktif.
     * Data periode STO lama tetap ada di database (arsip),
     * namun tidak akan muncul di UI manapun.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active_sto', function (Builder $builder) {
            $activeSto = app(ActiveStoService::class)->active();

            if ($activeSto) {
                $builder->where('scan_results.sto_code_id', $activeSto->id);
            }
        });
    }

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
        return $query->where('scan_results.user_id', $userId);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('scan_results.created_at', 'desc')->orderBy('scan_results.id', 'desc');
    }

    public function scopeToday($query)
    {
        return $query
            ->where('scan_results.created_at', '>=', now()->startOfDay())
            ->where('scan_results.created_at', '<=', now()->endOfDay());
    }

    public function getSizeAttribute(): string
    {
        if (in_array($this->shape_code, ['RF', 'RH'])) {
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
