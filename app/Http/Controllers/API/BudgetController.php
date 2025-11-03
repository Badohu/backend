<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BudgetController extends Controller
{
    use AuthorizesRequests; // Ensures access to the $this->authorize() method

    /**
     * Display a listing of budgets.
     * Corresponds to: GET /api/v1/budgets
     */
    public function index(): JsonResponse
    {
        // Policy Check: This checks the BudgetPolicy@viewAny method (FM/CEO allowed).
        $this->authorize('viewAny', Budget::class); 

        // Removed 'costCenter' relation and added 'project' (if applicable)
        $budgets = Budget::with(['department', 'project'])->latest()->get(); 

        return response()->json($budgets);
    }

    /**
     * Store a newly created budget request in storage (Submitted by Finance Manager).
     * Corresponds to: POST /api/v1/budgets
     */
    public function store(Request $request): JsonResponse
    {
        // Authorization: Checks BudgetPolicy@create (restricted to Finance Manager).
        // $this->authorize('create', Budget::class); 
        // \Log::info($request->all());
        $validated = $request->validate([
            // 🟢 Aligned with Budgets table migration
            'name' => 'required|string|max:255|unique:budgets,name',
            'department_id' => 'required|exists:departments,id',
            'amount_allocated' => 'required|numeric|min:0.01',
            'category' => 'required|in:CAPEX,OPEX,PROJECT',
            'year' => 'required|integer|min:2020',
            // Note: Removed 'total_amount', 'allocated_amount' (use amount_allocated), 'fiscal_year' (use year)
        ]);
        
        // 🟢 FIX 1: Set initial status to Pending for CEO approval
        $requestData = array_merge($validated, [
            'status' => 'Pending', 
            'amount_spent' => 0.00, // Explicitly set initial spent amount
        ]);

        $budget = Budget::create($requestData);

        

        return response()->json($budget, 201);
    }
    
    /**
     * Executive (CEO) approves the submitted budget request.
     * Corresponds to: POST /api/v1/budgets/{budget}/approve
     */
    public function approve(Budget $budget): JsonResponse
    {
        // Authorization: Checks BudgetPolicy@manage (restricted to CEO).
        $this->authorize('manage', $budget);
        
        if ($budget->status !== 'Pending') {
             return response()->json(['message' => 'Budget must be in Pending status to be approved.'], 400);
        }
        
        // 🟢 FIX 2: Update status to Active and record approval
        $budget->update([
            'status' => 'Active', 
            // 'approved_by_id' => auth()->id() // Requires column in budgets table
        ]);
        
        
        return response()->json($budget);
    }

    /**
     * Update the specified budget in storage (Used by CEO for adjustments).
     * Corresponds to: PUT/PATCH /api/v1/budgets/{budget}
     */
    public function update(Request $request, Budget $budget): JsonResponse
    {
        // Authorization: Checks BudgetPolicy@manage (restricted to CEO).
        // Using 'manage' instead of 'update' for consistency with our policy definitions.
        $this->authorize('manage', $budget);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:budgets,name,' . $budget->id,
            'amount_allocated' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|in:CAPEX,OPEX,PROJECT',
        ]);

        $budget->update($validated);

        return response()->json($budget);
    }

    /**
     * Custom Method: Get available funds for a specific department (Cost Center removed).
     * Corresponds to: GET /api/v1/budgets/available
     * (Supports Auto-validation/Real-time tracking features)
     */
    public function getAvailableFunds(): JsonResponse
    {
        // Policy Check: Ensure user has permission to view budget status (FM/CEO)
        $this->authorize('viewAny', Budget::class); 

        // 🟢 FIX 3: Removed cost_center_id and now aggregates by department.
        // This is a simplified example; a real implementation would need department_id from query params.
        
        // Assuming the query needs the available funds for the CURRENT user's department for simplicity.
        $departmentId = auth()->user->department_id;

        $budget = Budget::where('department_id', $departmentId)
            ->where('status', 'Active') // Only check against active budgets
            ->select('amount_allocated', 'amount_spent', 'status', 'name')
            ->get();
            
        // Simple aggregation for response
        $totalAllocated = $budget->sum('amount_allocated');
        $totalSpent = $budget->sum('amount_spent');
        $available = $totalAllocated - $totalSpent;

        return response()->json([
            'department_id' => $departmentId,
            'total_allocated' => $totalAllocated,
            'total_spent' => $totalSpent,
            'available_amount' => $available,
        ]);
    }
}