<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Link;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LinkPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('links.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('links.create');
    }

    public function update(Admin $admin, Link $link)
    {
        return $admin->can('links.update');
    }

    public function delete(Admin $admin, Link $link)
    {
        return $admin->can('links.delete');
    }
}
