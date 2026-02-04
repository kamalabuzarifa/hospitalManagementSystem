<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Core / shared
            ['name' => 'view_dashboard', 'description' => 'View dashboard'],
            ['name' => 'edit_profile', 'description' => 'Edit own profile'],

            // Coarse (legacy / compatibility)
            ['name' => 'manage_users', 'description' => 'Manage users (legacy)'],
            ['name' => 'manage_doctors', 'description' => 'Manage doctors (legacy)'],
            ['name' => 'manage_patients', 'description' => 'Manage patients (legacy)'],
            ['name' => 'manage_appointments', 'description' => 'Manage appointments (legacy)'],
            ['name' => 'manage_pharmacy', 'description' => 'Manage pharmacy (legacy)'],
            ['name' => 'view_reports', 'description' => 'View reports (legacy)'],
            ['name' => 'manage_settings', 'description' => 'Manage system settings (legacy)'],
            ['name' => 'view_access_logs', 'description' => 'View access logs (legacy)'],
            ['name' => 'manage_import_export', 'description' => 'Import/export dashboard data (legacy)'],
            ['name' => 'manage_inventory', 'description' => 'Manage inventory (legacy)'],
            ['name' => 'manage_prescriptions', 'description' => 'Manage prescriptions (legacy)'],
            ['name' => 'manage_notes', 'description' => 'Manage notes (legacy)'],

            // Fine-grained (recommended)
            ['name' => 'staff.view', 'description' => 'View staff'],
            ['name' => 'staff.create', 'description' => 'Create staff'],
            ['name' => 'staff.edit', 'description' => 'Edit staff'],
            ['name' => 'staff.delete', 'description' => 'Delete staff'],

            ['name' => 'doctors.view', 'description' => 'View doctors'],
            ['name' => 'doctors.create', 'description' => 'Create doctors'],
            ['name' => 'doctors.edit', 'description' => 'Edit doctors'],
            ['name' => 'doctors.delete', 'description' => 'Delete doctors'],

            ['name' => 'patients.view', 'description' => 'View patients'],
            ['name' => 'patients.create', 'description' => 'Create patients'],
            ['name' => 'patients.edit', 'description' => 'Edit patients'],
            ['name' => 'patients.delete', 'description' => 'Delete patients'],

            ['name' => 'appointments.view', 'description' => 'View appointments'],
            ['name' => 'appointments.create', 'description' => 'Create appointments'],
            ['name' => 'appointments.edit', 'description' => 'Edit appointments'],
            ['name' => 'appointments.delete', 'description' => 'Delete appointments'],
            ['name' => 'appointments.actions', 'description' => 'Appointment actions (checkin/cancel/complete/assist surgery)'],

            ['name' => 'inventory.view', 'description' => 'View inventory'],

            ['name' => 'pharmacy.view', 'description' => 'View pharmacy/medicines'],
            ['name' => 'pharmacy.create', 'description' => 'Create medicines'],
            ['name' => 'pharmacy.edit', 'description' => 'Edit medicines'],
            ['name' => 'pharmacy.delete', 'description' => 'Delete medicines'],

            ['name' => 'prescriptions.view', 'description' => 'View prescriptions'],
            ['name' => 'prescriptions.create', 'description' => 'Create prescriptions'],
            ['name' => 'prescriptions.edit', 'description' => 'Edit prescriptions'],
            ['name' => 'prescriptions.delete', 'description' => 'Delete prescriptions'],
            ['name' => 'prescriptions.dispense', 'description' => 'Dispense prescriptions'],

            ['name' => 'notes.create', 'description' => 'Create notes'],

            ['name' => 'reports.view', 'description' => 'View reports'],
            ['name' => 'settings.manage', 'description' => 'Manage settings'],
            ['name' => 'access_logs.view', 'description' => 'View access logs'],
            ['name' => 'import_export.manage', 'description' => 'Manage import/export'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
