<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ReturnRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReturnRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any return requests.
     */
    public function viewAny(Admin $admin)
    {
        return $admin->can('returns.index');
    }

    /**
     * Determine whether the admin can view the return request.
     */
    public function view(Admin $admin, ReturnRequest $returnRequest)
    {
        return $admin->can('returns.show');
    }

    /**
     * Determine whether the admin can approve the return request.
     */
    public function approve(Admin $admin, ReturnRequest $returnRequest)
    {
        return $admin->can('returns.approve');
    }

    /**
     * Determine whether the admin can mark return request as received.
     */
    public function received(Admin $admin, ReturnRequest $returnRequest)
    {
        return $admin->can('returns.received');
    }

    /**
     * Determine whether the admin can complete the return request.
     */
    public function complete(Admin $admin, ReturnRequest $returnRequest)
    {
        return $admin->can('returns.complete');
    }

    /**
     * Determine whether the admin can reject the return request.
     */
    public function reject(Admin $admin, ReturnRequest $returnRequest)
    {
        return $admin->can('returns.reject');
    }

    /**
     * Determine whether the admin can view reasons.
     */
    public function reasons(Admin $admin)
    {
        return $admin->can('returns.reasons');
    }

    /**
     * Determine whether the admin can store reasons.
     */
    public function reasonsStore(Admin $admin)
    {
        return $admin->can('returns.reasons.store');
    }

    /**
     * Determine whether the admin can destroy reasons.
     */
    public function reasonsDestroy(Admin $admin)
    {
        return $admin->can('returns.reasons.destroy');
    }

    /**
     * Determine whether the admin can toggle reasons.
     */
    public function reasonsToggle(Admin $admin)
    {
        return $admin->can('returns.reasons.toggle');
    }
}
