<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Admin', 'permission_level' => 'full_access'],
            ['name' => 'doctor', 'description' => 'Doctor', 'permission_level' => 'limited_access'],
            ['name' => 'receptionist', 'description' => 'Receptionist', 'permission_level' => 'limited_access'],
            ['name' => 'pharmacy', 'description' => 'Pharmacist', 'permission_level' => 'limited_access'],
            ['name' => 'patient', 'description' => 'Patient', 'permission_level' => 'limited_access'],
            ['name' => 'staff', 'description' => 'Staff', 'permission_level' => 'limited_access'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
