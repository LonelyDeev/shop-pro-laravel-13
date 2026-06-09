<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\City;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CityPolicy
{
    use HandlesAuthorization;

    public function create(Admin $admin)
    {
        return $admin->can('carriers.cities.create');
    }

    public function update(Admin $admin, City $city)
    {
        return $admin->can('carriers.cities.update');
    }

    public function delete(Admin $admin, City $city)
    {
        return $admin->can('carriers.cities.delete');
    }
}
