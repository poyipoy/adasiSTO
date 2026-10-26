<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class RackConfirmationHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'rack_confirmation_id', 'sto_code_id', 'location_id',
        'from_status', 'to_status', 'action_by_user_id', 'note', 'metadata',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Rack confirmation history bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Rack confirmation history bersifat append-only.'));
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function confirmation(): BelongsTo
    {
        return $this->belongsTo(RackConfirmation::class, 'rack_confirmation_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by_user_id');
    }
}
