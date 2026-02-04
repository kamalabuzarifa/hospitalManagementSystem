<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_email_and_password_and_role()
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'role_id' => $adminRole->id,
        ]);

        $response = $this->post('/login', [
            'username' => $admin->email,
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_doctor_can_login_with_email_and_password_and_role()
    {
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $doctor = User::factory()->create([
            'email' => 'doctor@example.com',
            'password' => bcrypt('doctor123'),
            'role_id' => $doctorRole->id,
        ]);

        $response = $this->post('/login', [
            'username' => $doctor->email,
            'password' => 'doctor123',
            'role' => 'doctor',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($doctor);
    }
}
