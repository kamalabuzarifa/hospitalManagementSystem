<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAppointmentScheduled extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $appointmentId,
        public readonly int $patientId,
        public readonly string $patientName,
        public readonly ?string $scheduledAtIso,
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
            'type' => 'appointment',
            'appointment_id' => $this->appointmentId,
            'patient_id' => $this->patientId,
            'patient_name' => $this->patientName,
            'scheduled_at' => $this->scheduledAtIso,
            'message_en' => 'New appointment scheduled for ' . $this->patientName,
            'message_ar' => 'تم حجز موعد جديد للمريض: ' . $this->patientName,
        ];
    }
}
