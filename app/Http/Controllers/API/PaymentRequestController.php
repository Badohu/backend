<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\Budget; 
use App\Models\AuditLog;
use App\Models\User; 

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

// Notifications 
use App\Notifications\RequestApproved; 
use App\Notifications\RequestPaid;
use App\Notifications\RequestRejected; 
use App\Notifications\RequestSubmitted;

class PaymentRequestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of requests, filtered, scoped, and paginated.
     * Corresponds to: GET /api/v1/requests
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Start the query with necessary relationships. 
        $query = PaymentRequest::with(['requester', 'department', 'approver']);

        // --- 2. Apply Filtering ---

        // Filter by Request Status (e.g., ?status=pending_approval)
        if ($request->has('status') && $request->status !== 'all') {
            // 🛑 FIX APPLIED: Correctly uses $request->status as a variable, not a literal string.
            $query->where('status', $request->status);
        }
        
        // Filter by search term (basic search on title/vendor)
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('vendor_name', 'like', $searchTerm);
            });
        }
        
        // Filter by Department ID (useful for Finance/CEO roles to switch departments)
        if ($request->has('department_id') && $request->department_id !== 'all') {
            $query->where('department_id', $request->department_id);
        }

        // --- 3. Implement Pagination ---
        $perPage = $request->input('per_page', 20); 

        $requests = $query->latest()
                      ->paginate($perPage);

        return response()->json($requests);
    }

    /**
     * Creates a new request (Draft status) and handles file upload.
     * Corresponds to: POST /api/v1/requests
     */
    public function store(Request $request): JsonResponse
    {
       // $this->authorize('create', PaymentRequest::class); 

        $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'amount' => 'required|numeric|min:0',
        'vendor_name' => 'required|string|max:255',
        'vendor_details' => 'nullable|array',
        'vendor_details.address' => 'nullable|string|max:255',
        'vendor_details.contact_person' => 'nullable|string|max:255',
        'vendor_details.phone' => 'nullable|string|max:20',
        'expense_category' => 'required|string',
        'department_id' => 'required|exists:departments,id'
]);


        $invoicePath = null;
        
        // Handle File Upload
        if ($request->hasFile('invoice_file')) {
            $file = $request->file('invoice_file');
            $invoicePath = $file->store('requests/invoices/' . Auth::id(), 'public');
        }

        $paymentRequest = PaymentRequest::create(array_merge($validated, [
            'user_id' => Auth::id(), 
            'status' => 'draft',
            'invoice_path' => $invoicePath, 
            'vendor_details' => $validated['vendor_details'] ?? null,
        ]));
        
        // Audit Log: Record creation
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $paymentRequest::class,
            'auditable_id' => $paymentRequest->id,
            'action' => 'created_draft',
            'new_values' => json_encode(['status' => 'draft']),
        ]);

        return response()->json($paymentRequest, 201);
    }
    
    /**
     * Method to change status from 'draft' to 'pending' (Submission).
     * Corresponds to: POST /api/v1/requests/{request}/submit
     */
    public function submit(PaymentRequest $paymentRequest): JsonResponse 
    {
        $this->authorize('submit', $paymentRequest); 

        $oldStatus = $paymentRequest->status;
        $paymentRequest->update(['status' => 'pending', 'submitted_at' => now()]);

        // Notify the Finance Manager (or appropriate approver)
        $financeManager = User::whereHas('role', fn($q) => $q->where('name', 'Finance Manager'))->first();
        if ($financeManager) {
             $financeManager->notify(new RequestSubmitted($paymentRequest)); 
        }

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $paymentRequest::class,
            'auditable_id' => $paymentRequest->id,
            'action' => 'submitted',
            'old_values' => json_encode(['status' => $oldStatus]),
            'new_values' => json_encode(['status' => 'pending']),
        ]);

        return response()->json($paymentRequest);
    }
    
    /**
     * Approval Workflow Action (Includes Budget Check).
     * Corresponds to: POST /api/v1/requests/{request}/approve
     */
    public function approve(Request $request, PaymentRequest $paymentRequest): JsonResponse 
    {
        $this->authorize('approve', $paymentRequest);

        // --- Budget Auto-Validation ---
        $budgetQuery = Budget::where('department_id', $paymentRequest->department_id)
            ->where('status', 'Active');
        if ($paymentRequest->project_id) {
             $budgetQuery->where('project_id', $paymentRequest->project_id);
        }
        
        $budget = $budgetQuery->first();

        if (!$budget) {
            return response()->json(['message' => 'Cannot approve. No active budget found for this expenditure.'], 400);
        }

        $availableAmount = $budget->amount_allocated - $budget->amount_spent;

        if ($paymentRequest->amount > $availableAmount) {
            // Log the reason for auto-rejection due to budget
            AuditLog::create([
                'user_id' => Auth::id(),
                'auditable_type' => $paymentRequest::class,
                'auditable_id' => $paymentRequest->id,
                'action' => 'rejected_auto_budget',
                'new_values' => json_encode(['reason' => 'Exceeds Budget Limit']),
            ]);
            
            return response()->json([
                'message' => 'Request exceeds the available budget limit and cannot be approved.',
                'available_budget' => $availableAmount,
            ], 403);
        }
        // --- End Budget Auto-Validation ---

        $oldStatus = $paymentRequest->status;
        
        $paymentRequest->update(['status' => 'approved', 'approved_at' => now(), 'approver_id' => Auth::id()]);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $paymentRequest::class,
            'auditable_id' => $paymentRequest->id,
            'action' => 'approved',
            'old_values' => json_encode(['status' => $oldStatus]),
            'new_values' => json_encode(['status' => 'approved']),
        ]);

        // Notification to Requester
        $paymentRequest->requester->notify(new RequestApproved($paymentRequest));

        return response()->json($paymentRequest);
    }

    /**
     * Rejection Workflow Action (Requires a rejection reason).
     * Corresponds to: POST /api/v1/requests/{request}/reject
     */
    public function reject(Request $request, PaymentRequest $paymentRequest): JsonResponse
    {
        $this->authorize('reject', $paymentRequest);
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500', // <-- REQUIRED REASON
        ]);

        $oldStatus = $paymentRequest->status;
        
        // Update status and store the rejection reason
        $paymentRequest->update([
            'status' => 'rejected', 
            'rejected_at' => now(), 
            'rejected_by_id' => Auth::id(),
            'rejection_reason' => $validated['rejection_reason'], // <-- Store the reason
        ]);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $paymentRequest::class,
            'auditable_id' => $paymentRequest->id,
            'action' => 'rejected',
            'old_values' => json_encode(['status' => $oldStatus]),
            'new_values' => json_encode(['status' => 'rejected', 'reason' => $validated['rejection_reason']]),
        ]);

        // Notification to Requester
       // $paymentRequest->requester->notify(new RequestRejected($paymentRequest));

        return response()->json($paymentRequest);
    }

    /**
     * Payment Action (Debits Budget and marks status as paid).
     * Corresponds to: POST /api/v1/requests/{request}/pay
     */
    public function markAsPaid(PaymentRequest $paymentRequest): JsonResponse
    {
        $this->authorize('pay', $paymentRequest);

        if ($paymentRequest->status !== 'approved') {
            return response()->json(['message' => 'Payment request must be approved before payment can be finalized.'], 400);
        }

        // Find the active budget
        $budgetQuery = Budget::where('department_id', $paymentRequest->department_id)
            ->where('status', 'Active');
        if ($paymentRequest->project_id) {
            $budgetQuery->where('project_id', $paymentRequest->project_id);
        }

        // Debit the budget's spent amount
        $budget = $budgetQuery->firstOrFail(); 
        $budget->amount_spent += $paymentRequest->amount;
        $budget->save();

        $oldStatus = $paymentRequest->status;

        // Update request status
        $paymentRequest->update(['status' => 'paid', 'paid_at' => now(), 'payee_id' => Auth::id()]);

        // Notification to Requester
        $paymentRequest->requester->notify(new RequestPaid($paymentRequest)); 

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $paymentRequest::class,
            'auditable_id' => $paymentRequest->id,
            'action' => 'marked_as_paid',
            'old_values' => json_encode(['status' => $oldStatus]),
            'new_values' => json_encode(['status' => 'paid']),
        ]);

        return response()->json($paymentRequest);
    }
} 