<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Assign all permissions to admin
        $adminRole = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();
        if ($adminRole) {
            // Do not wipe existing assignments; just ensure admin has everything.
            $adminRole->permissions()->syncWithoutDetaching($allPermissions->pluck('id'));
        }

        // Assign selected permissions to doctor
        $doctorRole = Role::where('name', 'doctor')->first();
        $doctorPermissions = Permission::whereIn('name', [
            'view_dashboard',
            'manage_patients',
            'manage_appointments',
            'manage_prescriptions',
            'manage_notes',
            'patients.view',
            'appointments.view',
            'prescriptions.view',
            'prescriptions.create',
            'prescriptions.edit',
            'prescriptions.delete',
            'notes.create',
            'edit_profile',
        ])->pluck('id');
        if ($doctorRole) {
            // Avoid wiping any customized permissions set via UI.
            $doctorRole->permissions()->syncWithoutDetaching($doctorPermissions);
        }

        // Assign selected permissions to receptionist
        $receptionistRole = Role::where('name', 'receptionist')->first();
        $receptionistPermissions = Permission::whereIn('name', [
            'view_dashboard',
            'manage_patients',
            'manage_appointments',
            'patients.view',
            'patients.create',
            'patients.edit',
            'patients.delete',
            'appointments.view',
            'appointments.create',
            'appointments.edit',
            'appointments.delete',
            'appointments.actions',
            'edit_profile',
        ])->pluck('id');
        if ($receptionistRole) {
            // Avoid wiping any customized permissions set via UI.
            $receptionistRole->permissions()->syncWithoutDetaching($receptionistPermissions);
        }

        // Assign selected permissions to pharmacy
        $pharmacyRole = Role::where('name', 'pharmacy')->first();
        $pharmacyPermissions = Permission::whereIn('name', [
            'view_dashboard',
            'manage_pharmacy',
            'manage_inventory',
            'manage_prescriptions',
            'inventory.view',
            'prescriptions.view',
            'prescriptions.dispense',
            'edit_profile',
        ])->pluck('id');
        if ($pharmacyRole) {
            // Avoid wiping any customized permissions set via UI.
            $pharmacyRole->permissions()->syncWithoutDetaching($pharmacyPermissions);
        }
    }
}
