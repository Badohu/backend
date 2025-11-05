<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            // ----------------------------------------------------
            // CEO Role
            // ----------------------------------------------------
            [
                'name' => 'CEO',
                'permissions' => [
                    'can_view_all_request' => true,
                    'can_approve_request' => true,
                    'can_reject_request' => true,
                    'can_view_all_budgets' => true,
                    'can_manage_budgets' => true,
                    'can_mark_as_paid' => true, // CEO can also execute payment
                    'can_create_request' => true,
                    'can_upload_documents' => true,
                    'can_create_budget' => true,
                    'can_create_user'=> true,
                    'can_view_audit_logs' => true,
                    'can_manage_roles' => true,
                    
                ],
            ],

            // ----------------------------------------------------
            // Finance Manager Role
            // ----------------------------------------------------
            [
                'name' => 'Finance Manager', 
                'permissions' => [
                    'can_view_all_budgets' => true,
                    'can_view_all_request' => true,
                    'can_create_request' => true, // Can submit requests
                    'can_create_budget' => true, // Can submit budget proposals
                    'can_upload_documents' => true, 
                    'can_manage_budgets' => true,
                    'can_create_user'=> true,
                ],
            ],
            
            // ----------------------------------------------------
            // Finance Officer Role
            // ----------------------------------------------------
            [
                'name' => 'Finance Officer',
                'permissions' => [
                    'can_view_all_request' => true,
                    'can_view_all_budgets' => true,
                    'can_create_request' => true,
                    'can_upload_documents' => true,
                ],
            ],
            
            // ----------------------------------------------------
            // HR Role (View-only for requests)
            // ----------------------------------------------------
            [
                'name' => 'HR',
                'permissions' => [
                    'can_view_all_request' => true, 
                    'can_create_request' => true,
                    'can_upload_documents' => true,
                    'can_view_all_budgets' => true,
                    'can_create_user'=> true,
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            // updateOrCreate expects $roleData to contain only column names as keys.
            // The JSON casting in the Role model handles the 'permissions' array.
            Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}