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
        'sto_session_id',
        'plant_id',
        'location_id',
        'barcode_material',
        'material_code',
        'material_name',
        'shape_code',
        'shape_name',
        'thickness',
        'width',
        'diameter',
        'length',
        'qty',
        'lot',
        'scan_time',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'thickness' => 'decimal:2',
            'width' => 'decimal:2',
            'diameter' => 'decimal:2',
            'length' => 'decimal:2',
            'qty' => 'integer',
            'scan_time' => 'datetime',
        ];
    }

    // ─── Relationships ───

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stoSession(): BelongsTo
    {
        return $this->belongsTo(StoSession::class);
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

    // ─── Scopes ───

    /**
     * Isolate data to a specific user (Rule 3).
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Default ordering: newest first (Rule 1).
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope for today's scans.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ─── Helpers ───

    /**
     * Get formatted size string based on shape.
     */
    public function getSizeAttribute(): string
    {
        if ($this->shape_code === 'RF') {
            return "{$this->thickness} x {$this->width} x {$this->length}";
        }

        if ($this->shape_code === 'RR') {
            return "Ø{$this->diameter} x {$this->length}";
        }

        return '-';
    }
}
