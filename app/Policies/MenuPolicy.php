<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('menus.index');
    }
    public function view(Admin $admin, Menu $menu)
    {
        return $admin->can('menus.update');
    }

    public function create(Admin $admin)
    {
        return $admin->can('menus.create');
    }

    public function update(Admin $admin, Menu $menu)
    {
        return $admin->can('menus.update');
    }

    public function delete(Admin $admin, Menu $menu)
    {
        return $admin->can('menus.delete');
    }
}
