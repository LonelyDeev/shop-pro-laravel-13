<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Story;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('stories.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('stories.create');
    }

    public function update(Admin $admin,Story $story)
    {
        return $admin->can('stories.update');
    }

    public function delete(Admin $admin,Story $story)
    {
        return $admin->can('stories.delete');
    }
}
