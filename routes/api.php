<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\PaymentRequestController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ProjectController; 
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;
// ----------------------------------------------------------------------
// 1. PUBLIC ROUTES (Authentication)
// ----------------------------------------------------------------------

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ----------------------------------------------------------------------
// 2. PROTECTED ROUTES (API v1)
// ----------------------------------------------------------------------

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    
    // --- User Profile & Notifications ---
    Route::get('/auth/user', [AuthController::class, 'showAuthenticatedUser']); 
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    

    // --- Dashboard Overview ---
    Route::get('/dashboard/overview', [DashboardController::class, 'index']);
// ...
    
// --- Commenting & Feedback (New Routes) ---
    // GET: Fetch all comments for a request
    Route::get('requests/{paymentRequest}/comments', [CommentController::class, 'index']);
    // POST: Add a new comment to a request
    Route::post('requests/{paymentRequest}/comments', [CommentController::class, 'store']);
    // --- Core Payment Request Management ---
    Route::apiResource('requests', PaymentRequestController::class);
    
    // Workflow Endpoints (Permissions mapped to Role Seeder abilities)
    
    // Submission: All roles have 'can_create_request'.
    Route::post('requests/{request}/submit', [PaymentRequestController::class, 'submit']);
    
    // Approval: Restricted strictly to the CEO (only role with 'can_approve_request').
    Route::post('requests/{request}/approve', [PaymentRequestController::class, 'approve'])
        ->middleware('role:CEO');
        
    // Rejection: Restricted strictly to the CEO (only role with 'can_reject_request').
    Route::post('requests/{request}/reject', [PaymentRequestController::class, 'reject'])
        ->middleware('role:CEO');
        
    // Payment Execution: Restricted strictly to the CEO (only role with 'can_mark_as_paid').
    Route::post('requests/{request}/pay', [PaymentRequestController::class, 'markAsPaid'])
        ->middleware('role:CEO'); // ⬅️ CEO is the only role with 'can_mark_as_paid'
    
    // Documents: All roles have 'can_upload_documents'.
    Route::post('requests/{request}/upload', [PaymentRequestController::class, 'uploadDocument']);
    
    
    // --- Budget & Financial Setup ---
    
    // CRUD for budgets (store=create). Restricted to roles with 'can_create_budget'.
    Route::apiResource('budgets', BudgetController::class)->only(['index', 'show', 'store', 'update'])
        ->middleware('role:Finance Manager,CEO'); //  FM & CEO have 'can_create_budget'
    
    // Budget Approval: Restricted strictly to the CEO (only role with 'can_manage_budgets').
    Route::post('budgets/{budget}/approve', [BudgetController::class, 'approve'])
        ->middleware('role:CEO');
    
    // Financial Tracking: Accessible to budget roles.
    Route::get('budgets/available/', [BudgetController::class, 'getAvailableFunds']) 
        ->middleware('role:Finance Manager,CEO'); 
    
    
    // --- Utility and Lookup Data ---
    Route::get('lookup/departments', [DepartmentController::class, 'index']);
    Route::get('lookup/roles', [RoleController::class, 'index']);
    Route::get('lookup/projects', [ProjectController::class, 'index']);
});