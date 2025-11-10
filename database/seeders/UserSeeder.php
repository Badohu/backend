<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Fetch Role and Department IDs
        $ceoRole = Role::where('name', 'CEO')->first();
        $financeManagerRole = Role::where('name', 'Finance Manager')->first();
        $financeOfficerRole = Role::where('name', 'Finance Officer')->first();
        $hrRole = Role::where('name', 'HR')->first();
        $requestorRole = Role::where('name', 'Requestor')->first(); // New Requestor role

        $ceoDept = Department::where('code', 'CEO')->first();
        $financeDept = Department::where('code', 'FIN')->first();
        $hrDept = Department::where('code', 'HR')->first();
        $techDept = Department::where('code', 'TECH')->first(); // Tech Department
        $marketingDept = Department::where('code', 'MKT')->first(); // Marketing Department

        // Guard against missing roles/departments (important if seeders run out of order)
        if (!$ceoRole || !$financeManagerRole || !$techDept) {
            echo "Warning: Required roles/departments not found. Check RoleSeeder/DepartmentSeeder.\n";
            return;
        }

        // ----------------------------------------------------
        // 1. CEO (Super Admin)
        // ----------------------------------------------------
        User::updateOrCreate(
            ['email' => 'ronald.ceo@opex.com'], // Use the confirmed CEO email
            [
                'name' => 'Ronald CEO',
                'password' => Hash::make('iamceo1234'), // Use the password you test with
                'role_id' => $ceoRole->id,
                'department_id' => $ceoDept->id,
            ]
        );

        // ----------------------------------------------------
        // 2. Finance Manager (Budget Management)
        // ----------------------------------------------------
        User::updateOrCreate(
            ['email' => 'godsway.fm@opex.com'],
            [
                'name' => 'Godsway Finance Manager',
                'password' => Hash::make('password'),
                'role_id' => $financeManagerRole->id,
                'department_id' => $financeDept->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kate.fo@opex.com'],
            [
                'name' => ' Kate Finance Officer',
                'password' => Hash::make('password'),
                'role_id' => $financeOfficerRole->id,
                'department_id' => $financeDept->id,
            ]
        );

        // ----------------------------------------------------
        // 3. HR (User Creation/Requesting)
        // ----------------------------------------------------
        User::updateOrCreate(
            ['email' => 'eunice.hr@opex.com'], // Use the confirmed HR email
            [
                'name' => 'Eunice HR',
                'password' => Hash::make('password'), // Use the generic password
                'role_id' => $hrRole->id,
                'department_id' => $hrDept->id,
            ]
        );
        
        // ----------------------------------------------------
        // 4. Tech Requestor (Departmental Scoping Test)
        // ----------------------------------------------------
        User::updateOrCreate(
            ['email' => 'tech.requestor@opex.com'],
            [
                'name' => 'Teddy Tech',
                'password' => Hash::make('password'),
                'role_id' => $requestorRole->id,
                'department_id' => $techDept->id,
            ]
        );
        
        // ----------------------------------------------------
        // 5. Marketing Requestor (Departmental Scoping Test)
        // ----------------------------------------------------
        User::updateOrCreate(
            ['email' => 'marketing.requestor@opex.com'],
            [
                'name' => 'Mary Marketing',
                'password' => Hash::make('password'),
                'role_id' => $requestorRole->id,
                'department_id' => $marketingDept->id,
            ]
        );
    }
}