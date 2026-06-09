<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SpecType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpecTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('products.spectypes');
    }

    public function update(Admin $admin, SpecType $specType)
    {
        return $admin->can('products.spectypes');
    }

    public function delete(Admin $admin, SpecType $specType)
    {
        return $admin->can('products.spectypes');
    }
}
