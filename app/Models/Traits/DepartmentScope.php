<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait DepartmentScope
{
    /**
     * Apply the departmental scope automatically when fetching records.
     */
    protected static function bootDepartmentScope()
    {
        // Check if the application is running in the console (like seeding or scheduled tasks)
        if (app()->runningInConsole() && !Auth::check()) {
            return;
        }

        static::addGlobalScope('departmental', function (Builder $builder) {
            $user = Auth::user();

            // Safety check: Should always be authenticated when hitting API
            if (!$user || !$user->role) {
                // Deny access if user or role is missing 
                $builder->whereRaw('1 = 0'); 
                return;
            }

            // Check for the "view all" permission (granted to CEO, FM, HR)
            $canViewAll = $user->role->permissions['can_view_all_department_data'] ?? false;
            
            if ($canViewAll) {
                // If they can view all, do nothing
                return; 
            }

            // For Requestors (Tech, Marketing, etc.):
            // Restrict the query to records matching the user's department ID
            $builder->where('department_id', $user->department_id);
        });
    }
}