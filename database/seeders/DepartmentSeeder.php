<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Executive Office', 'code' => 'CEO'],
          
        ];

        foreach ($departments as $data) {
            Department::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}