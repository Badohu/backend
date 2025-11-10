<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models (view the user list).
     */
    public function viewAny(User $user): bool
    {
        // Users who can create, manage, or view audit logs are generally allowed to see the user list.
        // We'll check for 'can_create_user' as the primary permission.
        return $user->role->permissions['can_create_user'] ?? false;
    }

    /**
     * Determine whether the user can create models (create a new user).
     */
    public function create(User $user): bool
    {
        // Only roles explicitly granted this permission (CEO, Finance Manager, HR) can create users.
        return $user->role->permissions['can_create_user'] ?? false;
    }

    /**
     * Determine whether the user can view the model.
     * (We don't need this method for user creation, but it's part of the standard policy.)
     */
    public function view(User $user, User $model): bool
    {
        // Allow users to view their own profile, or if they have overall view permission.
        return $user->id === $model->id || ($user->role->permissions['can_create_user'] ?? false);
    }
    
    // You would typically define 'update', 'delete', etc., here as well.
}