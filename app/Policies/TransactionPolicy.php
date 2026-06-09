<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('payments.transactions.index');
    }

    public function view(Admin $admin, Transaction $transaction)
    {
        return $admin->can('payments.transactions.view');
    }

    public function delete(Admin $admin, Transaction $transaction)
    {
        return $admin->can('payments.transactions.delete');
    }
}
