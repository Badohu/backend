<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Role; // <-- CRITICAL: Import the Role model

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. Fetch Necessary Role IDs ---
        // Fetching roles is necessary to set the default_role_id on departments
        $ceoRole = Role::where('name', 'CEO')->first();
        $financeManagerRole = Role::where('name', 'Finance Manager')->first();
        $hrRole = Role::where('name', 'HR')->first();
        
        // Fetch the new Requestor role ID
        $requestorRole = Role::where('name', 'Requestor')->first(); 
        
        // --- 2. Define All Departments with Default Roles ---
        $departments = [
            [
                'name' => 'Executive Office', 
                'code' => 'CEO',
                // Assign role ID for easy user creation later
                'default_role_id' => $ceoRole->id ?? null 
            ],
            [
                'name' => 'Finance', 
                'code' => 'FIN',
                'default_role_id' => $financeManagerRole->id ?? null
            ],
            [
                'name' => 'HR', 
                'code' => 'HR',
                'default_role_id' => $hrRole->id ?? null
            ],
            // 🛑 ADDED: Tech Department with Requestor Role
            [
                'name' => 'Tech', 
                'code' => 'TECH',
                'default_role_id' => $requestorRole->id ?? null 
            ],
            //  ADDED: Marketing Department with Requestor Role
            [
                'name' => 'Marketing', 
                'code' => 'MKT',
                'default_role_id' => $requestorRole->id ?? null
            ],
        ];

        foreach ($departments as $data) {
            // Ensure the Department model has 'default_role_id' in $fillable
            Department::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}