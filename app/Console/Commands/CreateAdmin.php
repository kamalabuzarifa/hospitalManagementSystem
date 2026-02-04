<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin {name} {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user with automatic password hashing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return 1;
        }

        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->error("Admin role not found! Run 'php artisan db:seed' first.");
            return 1;
        }

        // Create user with automatic password hashing
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,  // Will be auto-hashed by the User model
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user '{$name}' created successfully!");
        $this->line("Email: {$email}");
        $this->line("Password: {$password} (auto-encrypted in database)");

        return 0;
    }
}
