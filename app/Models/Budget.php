<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\DepartmentScope; 

class Budget extends Model
{
    use DepartmentScope; 

    protected $fillable = [
        'department_id',
        'project_id',
        'amount_allocated',
        'amount_spent', // Tracks money spent against the budget
        'status', // e.g., 'Active', 'Archived'
        'period_start',
        'period_end',
    ];
    
    // Ensure all monetary fields are cast as floats or decimals in your migration
    protected $casts = [
        'amount_allocated' => 'float', 
        'amount_spent' => 'float',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    // --- Relationships ---
    
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}