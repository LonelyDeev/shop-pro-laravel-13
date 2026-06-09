<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Attribute;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('attributes.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('attributes.create');
    }

    public function update(Admin $admin, Attribute $attribute)
    {
        return $admin->can('attributes.update');
    }

    public function delete(Admin $admin, Attribute $attribute)
    {
        return $admin->can('attributes.delete');
    }
}
