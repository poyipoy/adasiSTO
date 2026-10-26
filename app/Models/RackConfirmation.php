<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RackConfirmation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_FINISHED = 'finished';

    public const STATUS_VALIDATED = 'validated';

    public const STATUS_CONFIRMED = 'confirmed';

    protected $fillable = [
        'sto_code_id', 'location_id', 'status',
        'finished_by_user_id', 'finished_at',
        'validated_by_user_id', 'validated_at',
        'confirmed_by_user_id', 'confirmed_at', 'note',
    ];

    protected function casts(): array
    {
        return [
            'finished_at' => 'datetime',
            'validated_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function stoCode(): BelongsTo
    {
        return $this->belongsTo(StoCode::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function finishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finished_by_user_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RackConfirmationHistory::class);
    }
}
