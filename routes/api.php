<?php

use App\Http\Controllers\Api\AuthController;
 use App\Http\Controllers\Api\PaymentRequestController;
 use App\Http\Controllers\Api\BudgetController;
 use App\Http\Controllers\Api\UserController;
 use App\Http\Controllers\Api\DepartmentController;
 use App\Http\Controllers\Api\RoleController;
 use App\Http\Controllers\Api\ProjectController;
 use App\Http\Controllers\Api\CommentController;
 use App\Http\Controllers\Api\NotificationController;
 use App\Http\Controllers\Api\DashboardController;
 use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Api\LookupController; 
//use App\Http\Controllers\Api\ReportController; 

// ----------------------------------------------------------------------
// 1. PUBLIC ROUTES (Authentication)
// ----------------------------------------------------------------------

Route::post('/login', [AuthController::class, 'login'])->name('login');
// Logout is generally protected since it requires the user's token to invalidate it
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ----------------------------------------------------------------------
// 2. PROTECTED ROUTES (API v1)
// ----------------------------------------------------------------------

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    
    // --- User Profile & Notifications ---
    Route::get('/user', [AuthController::class, 'showAuthenticatedUser']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // --- Dashboard Overview ---
    Route::get('/dashboard/overview', [DashboardController::class, 'index']);

    // --- User Management (CRUD) ---
    // Restricted to HR and CEO for security
    Route::apiResource('users', UserController::class)
        ->middleware('role:HR,CEO'); 

    // --- Core Payment Request Management ---
    Route::apiResource('requests', PaymentRequestController::class);
    
    //new routes
    Route::post('/requests', [PaymentRequestController::class, 'store']);
    Route::get('/requests', [PaymentRequestController::class, 'index']);
    
    // --- Commenting & Feedback ---
    Route::get('requests/{paymentRequest}/comments', [CommentController::class, 'index']);
    Route::post('requests/{paymentRequest}/comments', [CommentController::class, 'store']);
    
    // --- Workflow Endpoints ---
    
    // Submission: All roles have 'can_create_request'
    Route::post('requests/{request}/submit', [PaymentRequestController::class, 'submit']);
    
    // Approval: Restricted to the CEO
    Route::post('requests/{request}/approve', [PaymentRequestController::class, 'approve'])
        ->middleware('role:CEO');
        
    // Rejection: Restricted to the CEO
    Route::post('requests/{request}/reject', [PaymentRequestController::class, 'reject'])
        ->middleware('role:CEO');
        
    // Payment Execution: Restricted to Finance Manager (who handles payment execution)
    Route::post('requests/{request}/pay', [PaymentRequestController::class, 'markAsPaid'])
        ->middleware('role:Finance Manager'); 
    
    // Documents:
    Route::post('requests/{request}/upload', [PaymentRequestController::class, 'uploadDocument']);
    
    
    // --- Budget & Financial Setup (CRUD) ---
    // Restricted to roles with budget management permissions
    Route::apiResource('budgets', BudgetController::class)->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middleware('role:Finance Manager,CEO');
    
    // Budget Approval: 
    Route::post('budgets/{budget}/approve', [BudgetController::class, 'approve'])
        ->middleware('role:CEO');
    
    Route::get('budgets/available', [BudgetController::class, 'getAvailableFunds']) 
        ->middleware('role:Finance Manager,CEO'); 
    
    // --- Project Management (CRUD) ---
    // Restricted to roles that manage budgets/projects
    Route::apiResource('projects', ProjectController::class)
        ->middleware('role:Finance Manager,CEO');

    // --- Utility and Lookup Data (Consolidated) ---
    Route::prefix('lookups')->group(function () {
        Route::get('departments', [DepartmentController::class, 'index']);
        Route::get('roles', [RoleController::class, 'index']);
        // Project lookup is simple list for dropdowns
        Route::get('projects', [ProjectController::class, 'index']); 
    });

});
