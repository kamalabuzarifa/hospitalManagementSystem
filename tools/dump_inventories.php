<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Inventory;

$rows = Inventory::all();
foreach ($rows as $r) {
    echo "id={$r->id}, medicine_id={$r->medicine_id}, quantity={$r->quantity}, minimum_threshold={$r->minimum_threshold}\n";
}
