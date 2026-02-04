<?php
// tools/check_pharmacy_notifications.php
// Bootstrap the Laravel app and inspect pharmacy notifications and a sample user.

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    echo "ERROR: vendor/autoload.php not found. Run composer install." . PHP_EOL;
    exit(1);
}
require $autoload;

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $roleId = DB::table('roles')->where('name', 'pharmacy')->value('id');
    echo "ROLE_ID:" . ($roleId ?? 'null') . PHP_EOL;

    $userId = $roleId ? DB::table('users')->where('role_id', $roleId)->value('id') : null;
    echo "PHARMACY_USER_ID:" . ($userId ?? 'null') . PHP_EOL;

    if ($userId) {
        $unreadCount = DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();
        echo "UNREAD_COUNT:" . $unreadCount . PHP_EOL;

        $rows = DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->limit(5)
            ->get();

        echo "SAMPLE_NOTIFICATIONS:\n";
        foreach ($rows as $r) {
            echo "- id={$r->id} created_at={$r->created_at} data={$r->data}\n";
        }
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}

