<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExportRequest extends Model
{
    use Prunable;
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'report',
        'format',
        'status',
        'filters',
        'file_disk',
        'file_path',
        'file_name',
        'mime_type',
        'total_rows',
        'error_message',
        'queued_at',
        'started_at',
        'completed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'total_rows' => 'integer',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }

    protected function pruning()
    {
        if ($this->file_path && Storage::disk($this->file_disk)->exists($this->file_path)) {
            Storage::disk($this->file_disk)->delete($this->file_path);
        }
    }
}
