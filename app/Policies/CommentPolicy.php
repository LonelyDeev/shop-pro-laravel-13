<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('comments.index');
    }

    public function view(Admin $admin, Comment $comment)
    {
        return $admin->can('comments.view');
    }

    public function update(Admin $admin, Comment $comment)
    {
        return $admin->can('comments.update');
    }

    public function delete(Admin $admin, Comment $comment)
    {
        return $admin->can('comments.delete');
    }
}
