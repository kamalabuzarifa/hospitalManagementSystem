<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Models\Settings;
use App\Models\User;
use App\Notifications\NewPrescriptionCreated;
use Illuminate\Support\Facades\Auth;

class PrescriptionObserver
{
    public function created(Prescription $prescription): void
    {
        // Ensure a stable prescription code exists for display across the app
        if (empty($prescription->prescription_code)) {
            $prescription->prescription_code = 'RX' . $prescription->id;
            // Save without firing created events again
            $prescription->saveQuietly();
        }

        $settings = Settings::query()->first();
        if ($settings && !$settings->pharmacy_dashboard_notifications) {
            return;
        }

        $actorId = Auth::id();

        $patientName = (string) ($prescription->appointment?->patient?->name
            ?? $prescription->patient?->name
            ?? '');

        $doctorName = (string) ($prescription->doctor?->user?->name ?? '');

        $recipients = User::query()
            ->whereHas('role', function ($query) {
                $query->where('name', 'pharmacy');
            })
            ->when($actorId, function ($query) use ($actorId) {
                $query->where('id', '!=', $actorId);
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = new NewPrescriptionCreated(
            (int) $prescription->id,
            $prescription->appointment_id ? (int) $prescription->appointment_id : null,
            $prescription->patient_id ? (int) $prescription->patient_id : null,
            $patientName,
            $doctorName,
        );

        foreach ($recipients as $user) {
            $user->notify($notification);
        }
    }
}
