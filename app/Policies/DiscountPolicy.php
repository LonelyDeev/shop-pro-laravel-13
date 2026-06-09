<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Discount;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiscountPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('discounts.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('discounts.create');
    }

    public function update(Admin $admin, Discount $discount)
    {
        return $admin->can('discounts.update');
    }

    public function delete(Admin $admin, Discount $discount)
    {
        return $admin->can('discounts.delete');
    }
}
