<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Settings;
use App\Models\User;
use App\Notifications\AppointmentDeleted;
use App\Notifications\AppointmentUpdated;
use App\Notifications\PatientDeleted;
use App\Notifications\PatientUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReceptionDoctorNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_update_and_delete_notify_reception_dashboard(): void
    {
        Notification::fake();

        $receptionRole = Role::create(['name' => 'receptionist']);
        $receptionUser = User::factory()->create(['role_id' => $receptionRole->id]);

        Settings::query()->create([
            'language' => 'en',
            'notification_new_patient' => true,
            'notification_new_appointment' => true,
            'allow_edit_appointments' => true,
            'allow_edit_patients' => true,
            'default_appointment_duration' => 20,
            'auto_assign_doctor' => false,
            'dark_mode' => false,
        ]);

        $patient = Patient::factory()->create(['name' => 'Test Patient']);

        $patient->update(['name' => 'Updated Patient']);
        Notification::assertSentTo($receptionUser, PatientUpdated::class);

        $patient->delete();
        Notification::assertSentTo($receptionUser, PatientDeleted::class);
    }

    public function test_appointment_update_and_delete_notify_doctor_and_reception(): void
    {
        Notification::fake();

        $receptionRole = Role::create(['name' => 'receptionist']);
        $doctorRole = Role::create(['name' => 'doctor']);

        $receptionUser = User::factory()->create(['role_id' => $receptionRole->id]);
        $doctorUser = User::factory()->create(['role_id' => $doctorRole->id]);

        $doctorProfile = Doctor::create([
            'user_id' => $doctorUser->id,
            'specialization' => 'General',
        ]);

        Settings::query()->create([
            'language' => 'en',
            'notification_new_patient' => true,
            'notification_new_appointment' => true,
            'allow_edit_appointments' => true,
            'allow_edit_patients' => true,
            'default_appointment_duration' => 20,
            'auto_assign_doctor' => false,
            'dark_mode' => false,
        ]);

        $patient = Patient::factory()->create(['name' => 'Appt Patient']);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorUser->id,
            'doctor_profile_id' => $doctorProfile->id,
            'date' => now()->toDateString(),
            'time' => '10:00:00',
            'scheduled_at' => now()->addHour(),
            'status' => 'scheduled',
            'reason' => 'Test',
        ]);

        $appointment->update([
            'scheduled_at' => now()->addHours(2),
        ]);

        Notification::assertSentTo($doctorUser, AppointmentUpdated::class);
        Notification::assertSentTo($receptionUser, AppointmentUpdated::class);

        $appointment->delete();

        Notification::assertSentTo($doctorUser, AppointmentDeleted::class);
        Notification::assertSentTo($receptionUser, AppointmentDeleted::class);
    }
}
