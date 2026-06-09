<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('roles.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('roles.create');
    }

    public function update(Admin $admin, Role $role)
    {
        return $admin->can('roles.update');
    }

    public function delete(Admin $admin, Role $role)
    {
        return $admin->can('roles.delete');
    }
}
