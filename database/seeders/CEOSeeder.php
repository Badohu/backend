<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CEOSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find the CEO Role (which now holds Superadmin permissions)
        $ceoRole = Role::where('name', 'CEO')->first();
        // Use global namespace for env() or ensure the helper is correctly loaded if error persists.
        $department = Department::where('name', 'Executive Office')->first() ?? Department::first();

        // Safety Check
        if (!$ceoRole || !$department) {
             $this->command->warn('Skipping CEO Seeder: Required Role or Department not found.');
             return;
        }

        // 2. Create the CEO User from ENV variables
        User::updateOrCreate(
     
            ['email' => trim(\env('SUPERADMIN_EMAIL'))],
            [
                // 🟢 FIX: Use \env()
                'name' => trim(\env('SUPERADMIN_NAME', 'CEO')),
                // 🟢 FIX: Use \env()
                'password' => Hash::make(trim(\env('SUPERADMIN_PASSWORD'))),
                'role_id' => $ceoRole->id, 
                'department_id' => $department->id,
            ]
        );
    }
}