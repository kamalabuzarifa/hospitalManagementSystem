<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

$doctorUserId = 3; // adjust if different
$user = User::find($doctorUserId);
if (!$user) {
    echo "User {$doctorUserId} not found\n";
    exit(1);
}

$notifications = $user->unreadNotifications()->latest()->limit(10)->get();
if ($notifications->isEmpty()) {
    echo "No unread notifications for user {$doctorUserId}\n";
    exit(0);
}

foreach ($notifications as $n) {
    $data = (array) ($n->data ?? []);
    $type = $data['type'] ?? null;
    $appointmentId = $data['appointment_id'] ?? null;
    $patientId = $data['patient_id'] ?? null;

    echo "Notification id={$n->id} type={$type} appointment_id={$appointmentId} patient_id={$patientId}\n";

    $viewUrl = null;
    if ($type === 'appointment' && $appointmentId) {
        if ($patientId) {
            $viewUrl = route('doctor.patients.show', [
                'patient' => $patientId,
                'appointment_id' => $appointmentId,
                'back' => url()->current(),
            ]);
        } else {
            // fallback
            try {
                $viewUrl = route('doctor.patients-records.show', $appointmentId);
            } catch (Exception $e) {
                $viewUrl = 'route error: '.$e->getMessage();
            }
        }
    } elseif ($type === 'prescription' && !empty($data['prescription_id'])) {
        try {
            $viewUrl = route('pharmacy.prescriptions.show', $data['prescription_id']);
        } catch (Exception $e) {
            $viewUrl = 'route error: '.$e->getMessage();
        }
    }

    echo "Computed viewUrl: " . ($viewUrl ?? 'null') . "\n\n";
}
