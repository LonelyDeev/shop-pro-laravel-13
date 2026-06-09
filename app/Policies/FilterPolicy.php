<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Filter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilterPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('filters.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('filters.create');
    }

    public function update(Admin $admin, Filter $filter)
    {
        return $admin->can('filters.update');
    }

    public function delete(Admin $admin, Filter $filter)
    {
        return $admin->can('filters.delete');
    }
}
