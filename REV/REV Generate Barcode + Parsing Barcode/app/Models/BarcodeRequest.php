<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarcodeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'sto_code_id',
        'plant_id',
        'location_id',
        'material_code',
        'material_name',
        'shape_code',
        'shape_name',
        'thickness',
        'width',
        'diameter',
        'length',
        'lot_number',
        'qty',
        'status',
        'rejection_reason',
        'generated_barcode_material',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'thickness'   => 'integer',
        'width'       => 'integer',
        'diameter'    => 'integer',
        'length'      => 'integer',
        'qty'         => 'integer',
    ];

    /**
     * Get the user who requested the barcode.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plant where the request is made.
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Get the location of the material.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the user who reviewed the request.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Get formatted size based on shape.
     */
    public function getSizeAttribute(): string
    {
        if (in_array($this->shape_code, ['RF', 'RH'])) {
            return "{$this->thickness} x {$this->width} x {$this->length}";
        }

        if ($this->shape_code === 'RR') {
            return "{$this->diameter} x {$this->length}";
        }

        return '-';
    }

    /**
     * Get the label description string for printing.
     * Format: "{shape_name} {dimensions}"
     * RF  → "Flat 6 x 50 x 3000"
     * RR  → "Round Ø25 x 3000"
     */
    public function getLabelDescriptionAttribute(): string
    {
        if (in_array($this->shape_code, ['RF', 'RH'])) {
            $shapeName = $this->shape_code === 'RF' ? 'Flat' : 'Hollow';
            return "{$shapeName} {$this->thickness} x {$this->width} x {$this->length}";
        }

        if ($this->shape_code === 'RR') {
            return "Round Ø{$this->diameter} x {$this->length}";
        }

        return $this->shape_name . ' ' . $this->getSizeAttribute();
    }

    /**
     * Get the full QR barcode string that was generated.
     * Format: "{barcode_material} | {lot_number} | {qty}"
     */
    public function getFullBarcodeAttribute(): string
    {
        if (!$this->generated_barcode_material || !$this->qty) {
            return '';
        }

        return "{$this->generated_barcode_material} | {$this->lot_number} | {$this->qty}";
    }
}
