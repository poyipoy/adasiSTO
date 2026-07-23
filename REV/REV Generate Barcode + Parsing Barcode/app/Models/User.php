<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'pass',
        'password',
        'role',
        'is_active',
        'is_validator',
    ];

    protected $hidden = [
        'pass',
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_validator' => 'boolean',
        ];
    }

    // ─── Relationships ───

    public function scanResults(): HasMany
    {
        return $this->hasMany(ScanResult::class);
    }

    public function scanResultLogs(): HasMany
    {
        return $this->hasMany(ScanResultLog::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function exportRequests(): HasMany
    {
        return $this->hasMany(ExportRequest::class);
    }

    // ─── Helpers ───

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isScanner(): bool
    {
        return $this->role === 'scanner';
    }

    public function isValidator(): bool
    {
        return $this->isAdmin() || ($this->isScanner() && $this->is_validator);
    }

    public function canAccessMaterialDouble(): bool
    {
        return $this->isAdmin() || $this->isValidator();
    }

    // ─── Scopes ───

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
