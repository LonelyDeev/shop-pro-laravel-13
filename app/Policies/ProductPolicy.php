<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('products.index');
    }

    public function create(Admin $admin)
    {
        return $admin->can('products.create');
    }

    public function update(Admin $admin, Product $product)
    {
        return $admin->can('products.update');
    }

    public function delete(Admin $admin, Product $product)
    {
        return $admin->can('products.delete');
    }
}
