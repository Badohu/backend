<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();

            // 🟢 ESSENTIAL: Links the request to the User who created it.
            // (Regardless of whether that user is a CEO, FM, or FO).
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // 🟢 FOREIGN KEYS (Constrained)
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            
         
            
            $table->string('title');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->string('vendor_name');
            $table->text('vendor_details')->nullable();
            $table->string('expense_category');

            // Workflow Status
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'paid'])->default('draft');
            $table->text('rejection_reason')->nullable();
            
            // Timestamp tracking
            //$table->timestamp('submitted_at')->nullable();
            //$table->timestamp('approved_at')->nullable();
            //$table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};