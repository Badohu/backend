<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\DepartmentScope; 

class PaymentRequest extends Model
{
    use DepartmentScope; 

    // Use $fillable to explicitly list all fields the controller can write to,
    // including the new 'invoice_path' and the workflow status/users.
    protected $fillable = [
        'user_id',            // The person who created the request (Requester)
        'department_id',
        'project_id',
        'title',
        'description',
        'amount',
        'invoice_path',       // For file upload path
        'vendor_name',        
        'expense_category',
        'status',             // e.g., 'draft', 'pending_approval', 'approved', 'paid'
        'approver_id',        // User who approves (e.g., CEO)
        'payee_id',           // User who marks as paid (e.g., Finance Officer)
        'rejected_by_id',     // User who rejected the request
        'submitted_at',       // Time when request moved from draft to pending
    ];

    protected $casts = [
        'vendor_details' => 'array', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];
   

    // --- Core Relationships ---
    
    /**
     * The request belongs to the user who created it.
     */
    public function requester(): BelongsTo
    {
        // Assuming the foreign key in the DB is 'user_id' as per controller logic.
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // --- Workflow Relationships ---

    /**
     * The request was approved by this user (e.g., CEO).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * The request was marked as paid by this user (e.g., Finance Officer).
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    /**
     * The request was rejected by this user.
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_id');
    }
}