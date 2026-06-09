<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BannerPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('banners.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('banners.create');
    }

    public function update(Admin $admin, Banner $banner)
    {
        return $admin->can('banners.update');
    }

    public function delete(Admin $admin, Banner $banner)
    {
        return $admin->can('banners.delete');
    }
}
