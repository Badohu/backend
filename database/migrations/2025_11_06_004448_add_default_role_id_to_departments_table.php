<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Add a foreign key column linked to the 'roles' table
            $table->foreignId('default_role_id')->nullable()->constrained('roles')->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Drop the foreign key constraint first (optional, but safer)
            $table->dropForeign(['default_role_id']); 
            $table->dropColumn('default_role_id');
        });
    }
};