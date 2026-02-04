<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Tests\TestCase;

class PharmacyDispenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacy_can_dispense_prescription_and_decrement_inventory(): void
    {
        $pharmacyRole = Role::create(['name' => 'pharmacy']);
        $doctorRole = Role::create(['name' => 'doctor']);
        $receptionRole = Role::create(['name' => 'receptionist']);

        $this->seed(PermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $pharmacyUser = User::factory()->create(['role_id' => $pharmacyRole->id]);
        $doctorUser = User::factory()->create(['role_id' => $doctorRole->id]);
        $receptionUser = User::factory()->create(['role_id' => $receptionRole->id]);

        $doctorProfile = Doctor::create([
            'user_id' => $doctorUser->id,
            'specialization' => 'General',
        ]);

        $patient = Patient::factory()->create();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorUser->id,
            'doctor_profile_id' => $doctorProfile->id,
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'status' => 'scheduled',
            'scheduled_at' => now()->addHour(),
            'reason' => 'Test',
        ]);

        $medicine = Medicine::create([
            'name' => 'Paracetamol',
            'sku' => 'PARA-001',
            'unit_price' => 5,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ]);

        Inventory::create([
            'medicine_id' => $medicine->id,
            'quantity' => 3,
            'minimum_threshold' => 1,
        ]);

        $prescription = Prescription::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctorProfile->id,
            'patient_id' => $patient->id,
            'medicine' => $medicine->name,
        ]);

        $this->actingAs($pharmacyUser)
            ->post(route('pharmacy.prescriptions.dispense', $prescription))
            ->assertStatus(302);

        $this->assertDatabaseHas('prescription_medicine', [
            'prescription_id' => $prescription->id,
            'medicine_id' => $medicine->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('inventories', [
            'medicine_id' => $medicine->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'dispensed_by_user_id' => $pharmacyUser->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $doctorUser->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $receptionUser->id,
        ]);
    }
}
