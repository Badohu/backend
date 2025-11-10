<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Checks if the authenticated user has any of the specified roles or permissions.
     *
     * @param  string ...$roles The list of allowed role names (e.g., 'CEO') or permission keys (e.g., 'can_create_budget').
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Authentication Check
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        $user = Auth::user();

        // 2. Role Existence Check
        if (!$user->role) {
            return response()->json(['message' => 'Access Denied. User role not defined.'], 403);
        }

        $userRoleName = $user->role->name;
        
        // 🛑 FIX: Use the attribute directly. It should be a PHP array 
        // because the 'permissions' column is cast to 'array' in the Role model.
        // Use ?? [] to default to an empty array if permissions is null.
        $permissions = $user->role->permissions ?? []; 
        
        // --- NOTE: Your existing code had this problematic line:
        // $permissions = json_decode($user->role->permissions ?? '{}', true);

        // 3. CEO/Superadmin Override (CRITICAL NEW LOGIC)
        // The CEO role grants universal access regardless of the specific argument provided.
        if ($userRoleName === 'CEO') {
            return $next($request);
        }
        
        // 4. Check Roles OR Permissions
        // Iterate through all required arguments (roles or permissions)
        foreach ($roles as $requiredRoleOrPermission) {
            
            // A. Check if the argument is a simple role name
            if ($userRoleName === $requiredRoleOrPermission) {
                return $next($request);
            }
            
            // B. Check if the argument is a permission key
            if (isset($permissions[$requiredRoleOrPermission]) && $permissions[$requiredRoleOrPermission] === true) {
                return $next($request);
            }
        }

        // 5. Deny access if no match is found
        return response()->json(['message' => 'Access Denied. Insufficient privileges for this action.'], 403);
    }
}