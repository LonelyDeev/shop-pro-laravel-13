<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('payments.currencies');
    }

    public function create(Admin $admin)
    {
        return $admin->can('payments.currencies');
    }

    public function update(Admin $admin, Currency $currency)
    {
        return $admin->can('payments.currencies');
    }

    public function delete(Admin $admin, Currency $currency)
    {
        return $admin->can('payments.currencies');
    }
}
