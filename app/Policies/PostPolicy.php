<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('posts.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('posts.create');
    }

    public function update(Admin $admin, Post $post)
    {
        return $admin->can('posts.update');
    }

    public function delete(Admin $admin, Post $post)
    {
        return $admin->can('posts.delete');
    }
}
