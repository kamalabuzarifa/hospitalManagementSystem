<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void

    {
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RolePermissionSeeder::class);
        // Fetch admin role after seeding roles
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        User::firstOrCreate(
            ['email' => 'kamalabuzarifa@gmail.com'],
            [
                'name' => 'Admin',
                'phone' => '0599874071',
                'password' => '$2y$12$2a3MtgFWWPfIpTunk/HwZOLDF7cGl5SdokppUXIGbzMtTa4kOWHCS',//admin@1999
                'role_id' => $adminRole ? $adminRole->id : 1,
            ]
        );
    }
}