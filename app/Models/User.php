<?php

namespace App\Models;

// app/Models/User.php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ... default User fields ...
    protected $fillable = ['name', 'email', 'password', 'role_id', 'department_id', ];

    // Relationships based on the foreign keys in the migration
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

   

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class, 'requester_id');
    }

   
public function auditLogs()
{
    return $this->hasMany(\App\Models\AuditLog::class);
}
}
