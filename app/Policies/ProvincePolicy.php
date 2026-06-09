<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Province;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProvincePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('carriers.provinces.index');
    }

    public function view(Admin $admin, Province $province)
    {
        return $admin->can('carriers.provinces.show');
    }

    public function create(Admin $admin)
    {
        return $admin->can('carriers.provinces.create');
    }

    public function update(Admin $admin, Province $province)
    {
        return $admin->can('carriers.provinces.update');
    }

    public function delete(Admin $admin, Province $province)
    {
        return $admin->can('carriers.provinces.delete');
    }
}
