<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $appointmentId,
        public readonly int $patientId,
        public readonly string $patientName,
        public readonly ?string $scheduledAtIso,
        public readonly ?string $status,
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
            'type' => 'appointment_updated',
            'appointment_id' => $this->appointmentId,
            'patient_id' => $this->patientId,
            'patient_name' => $this->patientName,
            'scheduled_at' => $this->scheduledAtIso,
            'status' => $this->status,
            'message_en' => 'Appointment updated : ' . $this->patientName,
            'message_ar' => 'تم تعديل الموعد للمريض: ' . $this->patientName,
        ];
    }
}
