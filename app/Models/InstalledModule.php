<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstalledModule extends Model
{
    use HasFactory;

    protected $table = 'installed_modules';

    protected $fillable = [
        'slug', 'name', 'version', 'license_key',
        'license_expires_at', 'installed_at', 'updated_at',
        'is_active', 'status', 'last_error',
    ];

    protected $casts = [
        'license_expires_at' => 'datetime',
        'installed_at'       => 'datetime',
        'updated_at'         => 'datetime',
        'is_active'          => 'boolean',
    ];

    /* ---------------- Constants ---------------- */

    public const STATUS_INSTALLED = 'installed';
    public const STATUS_UPDATING  = 'updating';
    public const STATUS_FAILED    = 'failed';

    /* ---------------- Relationships ---------------- */

    public function cache()
    {
        return $this->belongsTo(PackageCache::class, 'slug', 'slug');
    }

    public function logs()
    {
        return $this->hasMany(ModuleInstallLog::class, 'module_slug', 'slug');
    }

    /* ---------------- Helpers ---------------- */

    public function isExpired(): bool
    {
        return $this->license_expires_at && $this->license_expires_at->isPast();
    }

    public function isUpdatable(): bool
    {
        return $this->cache
            && version_compare($this->cache->latest_version, $this->version, '>');
    }

    public function markAsUpdating(): void
    {
        $this->update(['status' => self::STATUS_UPDATING, 'last_error' => null]);
    }

    public function markAsInstalled(string $version): void
    {
        $this->update([
            'version'     => $version,
            'status'      => self::STATUS_INSTALLED,
            'updated_at'  => now(),
            'last_error'  => null,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status'     => self::STATUS_FAILED,
            'last_error' => $error,
        ]);
    }
}
