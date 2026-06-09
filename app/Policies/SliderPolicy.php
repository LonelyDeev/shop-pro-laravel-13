<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SliderPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('sliders.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('sliders.create');
    }

    public function update(Admin $admin, Slider $slider)
    {
        return $admin->can('sliders.update');
    }

    public function delete(Admin $admin, Slider $slider)
    {
        return $admin->can('sliders.delete');
    }
}
