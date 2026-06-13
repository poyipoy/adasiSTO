<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanResultLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'scan_result_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
    ];

    public $timestamps = false;

    // ─── Relationships ───

    public function scanResult(): BelongsTo
    {
        return $this->belongsTo(ScanResult::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
