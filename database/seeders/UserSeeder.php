<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get IDs of required roles and department
        $financeOfficerRole = Role::where('name', 'Finance Officer')->firstOrFail();
        $financeManagerRole = Role::where('name', 'Finance Manager')->firstOrFail();
        $HR_Role = Role::where('name', 'HR')->firstOrFail();
        $ceoRole = Role::where('name', 'CEO')->firstOrFail();
        $financeDept = Department::where('code', 'FIN')->firstOrFail();
        $HRDept = Department::where('code', 'HR')->firstOrFail();
        //$CEODept =Department::where('code', 'CEO')->firstOrFail();

        // --- 1. Finance Officer (Payment Execution) ---
        User::create([
            'name' => 'Aseda Finance Officer',
            'email' => 'aseda.fo@opex.com',
            'password' => Hash::make('password'), 
            'role_id' => $financeOfficerRole->id,
            'department_id' => $financeDept->id, 
          
        ]);

        // --- 2. Finance Manager (Initial Approval/Budget Submitter) ---
        User::create([
            'name' => 'Godsway Finance Manager',
            'email' => 'godsway.fm@opex.com',
            'password' => Hash::make('password'),
            'role_id' => $financeManagerRole->id,
            'department_id' => $financeDept->id,
           
        ]);

        //----3. HR (Request Oversight) ---
         User::create([
            'name' => 'Eunice HR',
            'email' => 'eunice.hr@opex.com',
            'password' => Hash::make('password'),
            'role_id' => $HR_Role->id,
            'department_id' => $HRDept->id,
        ]);
        /* --- 3. CEO (Final Approval/Budget Management) ---
        User::create([
            'name' => 'Ronald CEO',
            'email' => 'ronald.ceo@opex.com',
            'password' => Hash::make('password'),
            'role_id' => $ceoRole->id,
            'department_id' => $CEODept->id,*/
             
        
        
    }
}