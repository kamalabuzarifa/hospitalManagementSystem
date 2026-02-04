<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\Settings;
use App\Models\User;
use App\Notifications\AppointmentDeleted;
use App\Notifications\AppointmentUpdated;
use App\Notifications\NewAppointmentScheduled;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $settings = Settings::query()->first();
        if (!$settings?->notification_new_appointment) {
            return;
        }

        $appointment->loadMissing(['patient', 'doctor']);

        $doctorUserId = (int) ($appointment->doctor_id ?? 0);

        $doctorUser = $doctorUserId > 0
            ? User::query()->whereKey($doctorUserId)->first()
            : null;

        // Doctor can disable in-app notifications from their settings
        if ($doctorUser && $appointment->doctor && !$appointment->doctor->dashboard_notifications) {
            $doctorUser = null;
        }

        $patientName = (string) ($appointment->patient?->name ?? '');

        $notification = new NewAppointmentScheduled(
            (int) $appointment->id,
            (int) $appointment->patient_id,
            $patientName,
            $appointment->scheduled_at?->toISOString(),
        );

        $notifiedUserIds = [];

        if ($doctorUser) {
            $doctorUser->notify($notification);
            $notifiedUserIds[] = (int) $doctorUser->id;
        }

        if ($settings->reception_dashboard_notifications) {
            $receptionUsers = User::query()
                ->whereHas('role', fn ($q) => $q->where('name', 'receptionist'))
                ->get();

            foreach ($receptionUsers as $receptionUser) {
                if (in_array((int) $receptionUser->id, $notifiedUserIds, true)) {
                    continue;
                }
                $receptionUser->notify($notification);
            }
        }
    }

    public function updated(Appointment $appointment): void
    {
        $settings = Settings::query()->first();
        if (!$settings?->notification_new_appointment) {
            return;
        }

        $appointment->loadMissing(['patient', 'doctor']);

        // Avoid noisy notifications on unrelated updates
        if (!$appointment->wasChanged(['scheduled_at', 'date', 'time', 'status', 'doctor_id', 'doctor_profile_id'])) {
            return;
        }

        $patientName = (string) ($appointment->patient?->name ?? '');
        $notification = new AppointmentUpdated(
            appointmentId: (int) $appointment->id,
            patientId: (int) $appointment->patient_id,
            patientName: $patientName,
            scheduledAtIso: $appointment->scheduled_at?->toISOString(),
            status: $appointment->status,
        );

        $notifiedUserIds = [];

        $doctorUserId = (int) ($appointment->doctor_id ?? 0);
        if ($doctorUserId > 0) {
            $doctorUser = User::query()->whereKey($doctorUserId)->first();
            if ($doctorUser) {
                if (!$appointment->doctor || $appointment->doctor->dashboard_notifications) {
                    $doctorUser->notify($notification);
                    $notifiedUserIds[] = (int) $doctorUser->id;
                }
            }
        }

        if ($settings->reception_dashboard_notifications) {
            $receptionUsers = User::query()
                ->whereHas('role', fn ($q) => $q->where('name', 'receptionist'))
                ->get();

            foreach ($receptionUsers as $receptionUser) {
                if (in_array((int) $receptionUser->id, $notifiedUserIds, true)) {
                    continue;
                }
                $receptionUser->notify($notification);
            }
        }
    }

    public function deleted(Appointment $appointment): void
    {
        $settings = Settings::query()->first();
        if (!$settings?->notification_new_appointment) {
            return;
        }

        $appointment->loadMissing(['patient', 'doctor']);

        $patientName = (string) ($appointment->patient?->name ?? '');
        $notification = new AppointmentDeleted(
            appointmentId: (int) $appointment->id,
            patientId: (int) $appointment->patient_id,
            patientName: $patientName,
            scheduledAtIso: $appointment->scheduled_at?->toISOString(),
        );

        $notifiedUserIds = [];

        $doctorUserId = (int) ($appointment->doctor_id ?? 0);
        if ($doctorUserId > 0) {
            $doctorUser = User::query()->whereKey($doctorUserId)->first();
            if ($doctorUser) {
                if (!$appointment->doctor || $appointment->doctor->dashboard_notifications) {
                    $doctorUser->notify($notification);
                    $notifiedUserIds[] = (int) $doctorUser->id;
                }
            }
        }

        if ($settings->reception_dashboard_notifications) {
            $receptionUsers = User::query()
                ->whereHas('role', fn ($q) => $q->where('name', 'receptionist'))
                ->get();

            foreach ($receptionUsers as $receptionUser) {
                if (in_array((int) $receptionUser->id, $notifiedUserIds, true)) {
                    continue;
                }
                $receptionUser->notify($notification);
            }
        }
    }
}
