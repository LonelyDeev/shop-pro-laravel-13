<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageCache extends Model
{
    use HasFactory;

    protected $table = 'packages_cache';

    protected $fillable = [
        'slug', 'name', 'description', 'latest_version', 'author',
        'category', 'thumbnail', 'price', 'is_free', 'meta',
        'versions', 'fetched_at',
    ];

    protected $casts = [
        'meta'       => 'array',
        'versions'   => 'array',
        'is_free'    => 'boolean',
        'price'      => 'integer',
        'fetched_at' => 'datetime',
    ];

    /* ---------------- Relationships ---------------- */

    public function installedModule()
    {
        return $this->hasOne(InstalledModule::class, 'slug', 'slug');
    }

    /* ---------------- Helpers ---------------- */

    public function isInstalled(): bool
    {
        return $this->installedModule()->exists();
    }

    public function hasUpdate(): bool
    {
        $installed = $this->installedModule;

        return $installed && version_compare($this->latest_version, $installed->version, '>');
    }

    public function isExpired(): bool
    {
        $installed = $this->installedModule;

        return $installed
            && $installed->license_expires_at
            && $installed->license_expires_at->isPast();
    }
}
