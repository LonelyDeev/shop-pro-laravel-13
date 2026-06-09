<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('users.index');
    }

    public function view(Admin $admin, User $model)
    {
        return $admin->can('users.view');
    }

    public function create(Admin $admin)
    {
        return $admin->can('users.create');
    }

    public function update(Admin $admin, User $model)
    {
        return $admin->can('users.update') && ($model->level != 'creator');
    }

    public function delete(Admin $admin, User $model)
    {
        return $admin->can('users.delete') && ($model->level != 'creator');
    }
}
