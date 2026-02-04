<?php
// tools/send_test_prescription_notification.php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Notifications\NewPrescriptionCreated;

$targetUserId = 6; // pharmacy user from earlier check
$user = User::find($targetUserId);
if (!$user) {
    echo "User not found: {$targetUserId}\n";
    exit(1);
}

$notification = new NewPrescriptionCreated(
    prescriptionId: 999999,
    appointmentId: null,
    patientId: null,
    patientName: 'Test Patient',
    doctorName: 'Test Doctor',
);

$user->notify($notification);

echo "Sent test NewPrescriptionCreated notification to user {$targetUserId}\n";
