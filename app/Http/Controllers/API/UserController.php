<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Notifications\NewUserAccount; 
use App\Models\Role;
use App\Models\Department; // <-- Added Import
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    // ... existing login method ...

    // --- NEW: Index method for fetching users (for completeness) ---
    public function index(): JsonResponse
    {
        // 1. Authorization: Ensure the logged-in user can view users
        $this->authorize('viewAny', User::class); // Assuming 'can_view_all_users' permission is checked in the policy

        $users = User::with(['role', 'department'])->get(); // Fetch all users with relationships

        return response()->json($users);
    }

    // --- NEW: Store method for creating new users ---
    public function store(Request $request): JsonResponse
    {
        // 1. Authorization Check (Ensures only permitted roles can proceed)
        // Checks if the logged-in user has the permission to create users.
        $this->authorize('create', User::class); 

        // 2. Full Validation
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'required|exists:departments,id',
        ]);
        
        // 3. Generate and Hash Password
        $temporaryPassword = Str::random(12); // Generates a secure random password

        // 4. Create User
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            // Store the HASHED version in the database
            'password' => Hash::make($temporaryPassword), 
            'role_id' => $validatedData['role_id'],
            'department_id' => $validatedData['department_id'],
        ]);
        
        // 5. Send Notification (Email)
        try {
            // Send the PLAIN TEXT password via email using the NewUserAccount notification
            $user->notify(new NewUserAccount($temporaryPassword)); 
        } catch (\Exception $e) {
            // Log the error if mail configuration fails, but allow user creation to proceed
            Log::error('Mail failed for new user: ' . $user->email . ' Error: ' . $e->getMessage());
        }

        // 6. Return Response
        return response()->json([
            'message' => 'User created successfully and credentials emailed.',
            'user' => $user->load(['role', 'department']) // Return the new user data
        ], 201);
    }
}