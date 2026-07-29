<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Admin;
use App\Models\Seller;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        if (!$this->app->runningInConsole() && application_installed()) {
            foreach ($this->getPermissions() as $permission) {
                Gate::define($permission->name, function ($user) use ($permission) {
                    // بررسی اگر کاربر ادمین است
                    if ($user instanceof Admin) {
                        return $user->level == 'creator' || $user->hasRole($permission->roles);
                    }

                    // بررسی اگر کاربر فروشنده است
                    if ($user instanceof Seller) {
                        // فروشنده‌ها به همه چیز دسترسی دارند (یا منطق دلخواه)
                        return true;
                    }

                    return false;
                });
            }
        }
    }

    protected function getPermissions()
    {
        return Permission::where('active', true)->with('roles')->get();
    }
}
