<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Who performed the action
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 

            // What entity was affected (e.g., PaymentRequest:5)
            $table->string('auditable_type'); 
            $table->unsignedBigInteger('auditable_id');
            $table->index(['auditable_type', 'auditable_id']);

            // The action performed
            $table->string('action'); // e.g., 'submitted', 'approved', 'rejected', 'edited'

            // Details of the change
            $table->json('old_values')->nullable(); // Original data before action
            $table->json('new_values')->nullable(); // New data after action

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};