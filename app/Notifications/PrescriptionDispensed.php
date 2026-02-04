<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PrescriptionDispensed extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $prescriptionId,
        public readonly ?int $appointmentId,
        public readonly ?int $patientId,
        public readonly string $patientName,
        public readonly string $doctorName,
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
            'type' => 'dispense',
            'prescription_id' => $this->prescriptionId,
            'appointment_id' => $this->appointmentId,
            'patient_id' => $this->patientId,
            'patient_name' => $this->patientName,
            'doctor_name' => $this->doctorName,
            'message_en' => 'Prescription dispensed for ' . $this->patientName,
            'message_ar' => 'تم صرف الوصفة للمريض: ' . $this->patientName,
        ];
    }
}
