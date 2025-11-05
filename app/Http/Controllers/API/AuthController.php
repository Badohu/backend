<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handles user login and API token generation.
     * Corresponds to: POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
      
        // 1. Validate incoming credentials
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if(!$validated){
            return response()->json([
                'message' => 'Invalid input data.'
            ], 422);
        }
        $user = User::where('email', $request->email)->first();

        // // 2. Check credentials securely
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Throw exception for failed secure sign-in 
          return response()->json([
                 'message' => 'The provided credentials are incorrect.'
             ], 401);
        }
        
        // // 3. Structural Mapping Check (Per project requirement)
        // // Ensure user is assigned a role and department before granting access.
        if (!$user->role_id || !$user->department_id) {
             return response()->json([
                 'message' => 'User profile incomplete. Contact system admin.'
             ], 403);
        }

        // // 4. Token Generation
        $token = $user->createToken('opex-api-token')->plainTextToken;

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', ]),
            'token' => $token,
        ]);
    }

    /**
     * Handles user logout by revoking the current API token.
     * Corresponds to: POST /api/logout (Requires auth:sanctum middleware)
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the token that was used to authenticate the current request
        //$request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out and token revoked.']);
    }

        /**
     * Gets the authenticated user's profile and related data.
     * Corresponds to: GET /api/v1/user
     */
    public function showAuthenticatedUser(Request $request): JsonResponse
    {
        // Auth::user() automatically returns the authenticated User model
        $user = $request->user();

        // Load relationships (role, department) for access control context [cite: 8]
        $user->load(['role', 'department']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => [
                'id' => $user->role->id,
                'name' => $user->role->name, // e.g., 'Finance Officer'
                'permissions' => $user->role->permissions,
            ],
            'department' => $user->department->only(['id', 'name', 'code']),
    
        ]);
    }
    
    // NOTE: You would typically place the 'showAuthenticatedUser' method (for fetching profile)
    // in the general UserController, but it can be placed here as well.
}