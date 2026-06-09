<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('tags.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('tags.create');
    }
    public function view(Admin $admin, Tag $tag)
    {
        return $admin->can('tags.view');
    }
    public function update(Admin $admin, Tag $tag)
    {
        return $admin->can('tags.update');
    }

    public function delete(Admin $admin, Tag $tag)
    {
        return $admin->can('tags.delete');
    }
}
