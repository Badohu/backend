<?php

namespace App\Policies;

use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentRequestPolicy
{
    // The "CEO" should be granted all access via the 'before' method (recommended Laravel practice).
    // If you haven't implemented 'before', the CEO must pass the CheckRole middleware.

    /**
     * Determine whether the user can create models.
     * All general users should be able to initiate a request.
     */
    public function create(User $user): bool
    {
        // Assuming any logged-in user with a role can create a request.
        return true; 
    }

    /**
     * Determine whether the user can update the model.
     * Only the creator can update a request, and only if it's still a draft.
     */
    public function update(User $user, PaymentRequest $paymentRequest): bool
    {
        return $user->id === $paymentRequest->user_id && $paymentRequest->status === 'draft';
    }

    /**
     * Determine whether the user can submit the model (Draft -> Pending).
     * Only the creator can submit, and only if it's in draft status.
     */
    public function submit(User $user, PaymentRequest $paymentRequest): bool
    {
        // This is the critical missing method!
        return $user->id === $paymentRequest->user_id && $paymentRequest->status === 'draft';
    }

    /**
     * Determine whether the user can approve the model.
     * Approval is restricted to the CEO role (enforced by CheckRole middleware on the route).
     * This method ensures the request is in the correct lifecycle stage for approval.
     */
    public function approve(User $user, PaymentRequest $paymentRequest): bool
    {
        // CheckRole middleware on route should handle the user role check.
        // This policy check ensures the request is actually pending.
        return $paymentRequest->status === 'pending';
    }
    
    /**
     * Determine whether the user can reject the model.
     * Rejection is restricted to the CEO role (enforced by CheckRole middleware on the route).
     */
    public function reject(User $user, PaymentRequest $paymentRequest): bool
    {
        // Allow rejection if pending or approved (to stop the process)
        return in_array($paymentRequest->status, ['pending', 'approved']);
    }

    /**
     * Determine whether the user can mark the model as paid.
     * Payment is restricted to the Finance Manager (enforced by CheckRole middleware on the route).
     */
    public function pay(User $user, PaymentRequest $paymentRequest): bool
    {
        // Payment can only happen if the request has been approved.
        return $paymentRequest->status === 'approved';
    }

    // You can leave view/delete/forceDelete/restore as 'false' or implement them
    // based on whether you plan to expose those endpoints.
    // ... (rest of the Policy methods) ...
}