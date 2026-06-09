<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('admins.index');
    }

    public function view(Admin $admin, Admin $model)
    {
        return $admin->can('admins.view');
    }

    public function create(Admin $admin)
    {
        return $admin->can('admins.create');
    }

    public function update(Admin $admin, Admin $model)
    {
        return $admin->can('admins.update') && ($model->level != 'creator');
    }

    public function delete(Admin $admin, Admin $model)
    {
        return $admin->can('admins.delete') && ($model->level != 'creator');
    }
}
