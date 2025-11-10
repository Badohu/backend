<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\DepartmentScope; 

class Project extends Model
{
    use HasFactory, DepartmentScope; 

    protected $fillable = [
        'name',
        'department_id',
        'status', // <-- ADDED: Assuming projects need a status (e.g., Active, Completed)
    ];

    // --- Relationships ---

    /**
     * A Project belongs to a single Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * A Project can have many Payment Requests associated with it.
     */
    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    /**
     * A Project can be part of multiple Budgets.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}