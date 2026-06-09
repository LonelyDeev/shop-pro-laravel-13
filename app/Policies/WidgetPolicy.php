<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use App\Models\Widget;
use Illuminate\Auth\Access\HandlesAuthorization;

class WidgetPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('themes.widgets');
    }

    public function create(Admin $admin)
    {
        return $admin->can('themes.widgets');
    }

    public function update(Admin $admin, Widget $widget)
    {
        return $admin->can('themes.widgets');
    }

    public function delete(Admin $admin, Widget $widget)
    {
        return $admin->can('themes.widgets');
    }
}
