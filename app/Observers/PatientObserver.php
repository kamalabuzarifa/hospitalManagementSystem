<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\Settings;
use App\Models\User;
use App\Notifications\NewPatientRegistered;
use App\Notifications\PatientDeleted;
use App\Notifications\PatientUpdated;

class PatientObserver
{
    public function created(Patient $patient): void
    {
        $settings = Settings::query()->first();
        if (!$settings?->notification_new_patient) {
            return;
        }

        if ($settings && !$settings->reception_dashboard_notifications) {
            return;
        }

        $recipients = User::query()
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['receptionist']);
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = new NewPatientRegistered((int) $patient->id, (string) $patient->name);

        foreach ($recipients as $user) {
            $user->notify($notification);
        }
    }

    public function updated(Patient $patient): void
    {
        $settings = Settings::query()->first();
        if (!$settings?->notification_new_patient) {
            return;
        }

        if ($settings && !$settings->reception_dashboard_notifications) {
            return;
        }

        $recipients = User::query()
            ->whereHas('role', fn ($query) => $query->whereIn('name', ['receptionist']))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = new PatientUpdated((int) $patient->id, (string) $patient->name);

        foreach ($recipients as $user) {
            $user->notify($notification);
        }
    }

    public function deleted(Patient $patient): void
    {
        $settings = Settings::query()->first();
        if (!$settings?->notification_new_patient) {
            return;
        }

        if ($settings && !$settings->reception_dashboard_notifications) {
            return;
        }

        $recipients = User::query()
            ->whereHas('role', fn ($query) => $query->whereIn('name', ['receptionist']))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        $notification = new PatientDeleted((int) $patient->id, (string) $patient->name);

        foreach ($recipients as $user) {
            $user->notify($notification);
        }
    }
}
