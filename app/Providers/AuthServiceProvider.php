<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (!$this->app->runningInConsole() && application_installed()) {

            foreach ($this->getPermissions() as $permission) {
                Gate::define($permission->name, function ($admin) use ($permission) {
                    return $admin->level == 'creator' or $admin->hasRole($permission->roles);
                });
            }
        }
    }

    protected function getPermissions()
    {
        return Permission::where('active', true)->with('roles')->get();
    }
}
