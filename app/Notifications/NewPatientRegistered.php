<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPatientRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $patientId,
        public readonly string $patientName,
    ) {
    }

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'patient',
            'patient_id' => $this->patientId,
            'patient_name' => $this->patientName,
            'message_en' => 'New patient registered: ' . $this->patientName,
            'message_ar' => 'تم تسجيل مريض جديد: ' . $this->patientName,
        ];
    }
}
