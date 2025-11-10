<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // ----------------------------------------------------
            // CEO Role 
            // ----------------------------------------------------
            [
                'name' => 'CEO',
                'permissions' => [
                    'can_view_all_department_data' => true, // Grant Global View
                    'can_view_all_request' => true,
                    'can_approve_request' => true,
                    'can_reject_request' => true,
                    'can_view_all_budgets' => true,
                    'can_manage_budgets' => true,
                    'can_mark_as_paid' => true,
                    'can_create_request' => true,
                    'can_upload_documents' => true,
                    'can_create_budget' => true,
                    'can_create_user'=> true,
                    'can_view_audit_logs' => true,
                    'can_manage_roles' => true,
                ],
            ],

            // ----------------------------------------------------
            // Finance Manager Role (Budget Oversight + Global Scope)
            // ----------------------------------------------------
            [
                'name' => 'Finance Manager', 
                'permissions' => [
                    'can_view_all_department_data' => true, // Grant Global View
                    'can_view_all_budgets' => true,
                    'can_view_all_request' => true,
                    'can_create_request' => true,
                    'can_create_budget' => true,
                    'can_upload_documents' => true, 
                    'can_manage_budgets' => true, // EXCLUSIVE BUDGET MANAGEMENT
                    'can_create_user'=> true,
                ],
            ],
            
            // ----------------------------------------------------
            // Finance Officer Role (Payment Execution + Global Scope)
            // ----------------------------------------------------
            [
                'name' => 'Finance Officer',
                'permissions' => [
                    'can_view_all_department_data' => true, // Grant Global View
                    'can_view_all_request' => true,
                    'can_view_all_budgets' => true,
                    'can_mark_as_paid' => true, // PAYMENT EXECUTION
                    'can_create_request' => true,
                    'can_upload_documents' => true,
                ],
            ],
            
            // ----------------------------------------------------
            // HR Role (User Management/Requesting + Global Scope)
            // ----------------------------------------------------
            [
                'name' => 'HR',
                'permissions' => [
                    'can_view_all_department_data' => true, // Grant Global View
                    'can_view_all_request' => true, 
                    'can_create_request' => true,
                    'can_upload_documents' => true,
                    'can_view_all_budgets' => true,
                    'can_create_user'=> true,
                ],
            ],

            // ----------------------------------------------------
            // Requestor Role (Departmental View ONLY)
            // ----------------------------------------------------
            [
                'name' => 'Requestor',
                'permissions' => [
                    'can_view_department_request' => true,
                    'can_create_request' => true,
                    'can_upload_documents' => true,
                    'can_view_department_budgets' => true,
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(['name' => $roleData['name']], $roleData);
        }
    }
}