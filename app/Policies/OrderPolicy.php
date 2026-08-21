<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('orders.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('orders.create');
    }

    public function view(Admin $admin, Order $order)
    {
        return $admin->can('orders.view');
    }
    public function update(Admin $admin, Order $order)
    {
        return $admin->can('orders.update');
    }

    public function delete(Admin $admin, Order $order)
    {
        return $admin->can('orders.delete');
    }
}
