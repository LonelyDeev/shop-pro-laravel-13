<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('tickets.index');
    }

    public function view(Admin $admin, Ticket $ticket)
    {
        return $admin->can('tickets.show');
    }

    public function create(Admin $admin)
    {
        return $admin->can('tickets.create');
    }

    public function update(Admin $admin, Ticket $ticket)
    {
        return $admin->can('tickets.update');
    }

    public function delete(Admin $admin, Ticket $ticket)
    {
        return $admin->can('tickets.delete');
    }
}
