<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'name',
        'old_location_name',
        'description',
        'warehouse',
        'is_active',
        'created_by_user_id',
        // Confirmation fields (MISSION-03)
        'is_confirmed',
        'confirmed_by_user_id',
        'confirmed_at',
        'confirmation_note',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'is_confirmed' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function scanResults(): HasMany
    {
        return $this->hasMany(ScanResult::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('is_confirmed', true);
    }

    public function scopeUnconfirmed(Builder $query): Builder
    {
        return $query->where('is_confirmed', false);
    }
}
