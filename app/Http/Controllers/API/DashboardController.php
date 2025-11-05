<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Department;
use App\Models\Project;
use App\Models\Budget;
use App\Models\PaymentRequest;
use Illuminate\Http\JsonResponse;



class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        // --- 1. Authorization Scope (We still check if the user is authenticated) ---
        // CRITICAL: We remove the 'if (!$isCEO)' scope check here to get global numbers.
        
        // --- 2. Calculate Financials (Total Budget, Spent, Remaining) ---
        
        // BudgetQuery now ALWAYS targets ALL budgets
        $budgetQuery = Budget::query(); 

        $totalBudget = $budgetQuery->sum('amount_allocated');
        $totalSpent = $budgetQuery->sum('amount_spent');
        $remainingFunds = $totalBudget - $totalSpent;

        // --- 3. Calculate Entity Counts (ALL users see ALL counts) ---
        
        // COUNT ALL users, departments, and projects regardless of the user's role
        $userCount = User::count(); 
        $departmentCount = Department::count();
        $projectCount = Project::count(); 

        // --- 4. Fetch Recent Activity (Example - fetching company-wide) ---
        
        $recentUsers = User::with('role')->latest()->limit(5)->get(['id', 'name', 'role_id']); 
        $recentProjects = Project::latest()->limit(5)->get(); // No department filter

        // --- 5. Budget by Category (Company-wide) ---
        $budgetByCategory = Budget::select('category', DB::raw('SUM(amount_allocated) as total'))
                                   ->groupBy('category')
                                   ->get();

        // --- 6. Return Consolidated Data ---
        return response()->json([
            'metrics' => [
                'total_users' => $userCount,
                'total_departments' => $departmentCount,
                'total_projects' => $projectCount,
                'total_budget' => round($totalBudget, 2),
                'total_spent' => round($totalSpent, 2),
                'remaining_funds' => round($remainingFunds, 2),
                'percentage_spent' => $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 2) : 0,
            ],
            'recent_activity' => [
                'users' => $recentUsers,
                'projects' => $recentProjects,
                'departments' => Department::latest()->limit(5)->get(),
            ],
            'budget_breakdown' => $budgetByCategory,
        ]);
    }
}