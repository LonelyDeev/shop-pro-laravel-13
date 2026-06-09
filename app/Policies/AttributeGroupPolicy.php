<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\AttributeGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributeGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('attributes.groups.index');
    }

    public function view(Admin $admin, AttributeGroup $attributeGroup)
    {
        return $admin->can('attributes.groups.show');
    }

    public function create(Admin $admin)
    {
        return $admin->can('attributes.groups.create');
    }

    public function update(Admin $admin, AttributeGroup $attributeGroup)
    {
        return $admin->can('attributes.groups.update');
    }

    public function delete(Admin $admin, AttributeGroup $attributeGroup)
    {
        return $admin->can('attributes.groups.delete');
    }
}
