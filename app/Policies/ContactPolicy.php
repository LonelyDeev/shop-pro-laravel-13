<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return $admin->can('contacts.index');
    }

    public function view(Admin $admin, Contact $contact)
    {
        return $admin->can('contacts.view');
    }

    public function delete(Admin $admin, Contact $contact)
    {
        return $admin->can('contacts.delete');
    }
}
