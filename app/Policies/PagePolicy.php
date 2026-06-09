<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('pages.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('pages.create');
    }

    public function update(Admin $admin, Page $page)
    {
        return $admin->can('pages.update');
    }

    public function delete(Admin $admin, Page $page)
    {
        return $admin->can('pages.delete');
    }
}
