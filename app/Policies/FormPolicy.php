<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Form;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FormPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('forms.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('forms.create');
    }

    public function update(Admin $admin, Form $form)
    {
        return $admin->can('forms.update');
    }

    public function delete(Admin $admin, Form $form)
    {
        return $admin->can('forms.delete');
    }
}
