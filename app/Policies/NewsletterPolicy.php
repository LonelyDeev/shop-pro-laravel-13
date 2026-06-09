<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsletterPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('newsletters.index');
    }

    public function delete(Admin $admin, Newsletter $newsletter)
    {
        return $admin->can('newsletters.delete');
    }
}
