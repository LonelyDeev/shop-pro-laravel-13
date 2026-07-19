<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleInstallLog extends Model
{
    use HasFactory;

    protected $table = 'module_install_logs';

    protected $fillable = [
        'admin_id', 'module_slug', 'module_name', 'action',
        'from_version', 'to_version', 'status', 'message', 'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /* ---------------- Constants ---------------- */

    public const ACTION_INSTALL     = 'install';
    public const ACTION_UPDATE      = 'update';
    public const ACTION_UNINSTALL   = 'uninstall';
    public const ACTION_ACTIVATE    = 'activate';
    public const ACTION_DEACTIVATE  = 'deactivate';

    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    /* ---------------- Relationships ---------------- */

    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class);
    }
}
