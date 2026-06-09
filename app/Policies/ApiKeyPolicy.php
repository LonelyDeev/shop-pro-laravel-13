<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApiKeyPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('apikeys.index');
    }

    public function view(Admin $admin, Admin $model)
    {
        return $admin->can('apikeys.view');
    }

    public function create(Admin $admin)
    {
        return $admin->can('apikeys.create');
    }

    public function update(Admin $admin, Admin $model)
    {
        return $admin->can('apikeys.update') && ($model->level != 'creator');
    }

    public function delete(Admin $admin, Admin $model)
    {
        return $admin->can('apikeys.delete') && ($model->level != 'creator');
    }
}
