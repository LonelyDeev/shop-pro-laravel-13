<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SellerPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('sellers.index');
    }

    public function view(Admin $admin, Seller $seller)
    {
        return $admin->can('sellers.view');
    }

    public function create(Admin $admin)
    {
        return $admin->can('sellers.create');
    }

    public function update(Admin $admin, Seller $seller)
    {
        return $admin->can('sellers.update');
    }

    public function delete(Admin $admin, Seller $seller)
    {
        return $admin->can('sellers.delete');
    }
}
