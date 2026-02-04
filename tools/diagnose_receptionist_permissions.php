<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$role = \App\Models\Role::query()
    ->where('name', 'receptionist')
    ->first();

if (!$role) {
    echo "Role 'receptionist' not found\n";
    exit(1);
}

$hasViewDashboard = $role->permissions()->where('name', 'view_dashboard')->exists();
$hasEditProfile = $role->permissions()->where('name', 'edit_profile')->exists();

$permissionNames = $role->permissions()->pluck('name')->sort()->values();

echo "Receptionist role id: {$role->id}\n";
echo "has view_dashboard: " . ($hasViewDashboard ? 'YES' : 'NO') . "\n";
echo "has edit_profile: " . ($hasEditProfile ? 'YES' : 'NO') . "\n";
echo "perm count: " . $permissionNames->count() . "\n";
echo "perms: " . $permissionNames->implode(', ') . "\n\n";

$users = \App\Models\User::query()
    ->where('role_id', $role->id)
    ->select(['id', 'name', 'email', 'role_id'])
    ->orderBy('id')
    ->limit(10)
    ->get();

echo "Sample users with receptionist role (up to 10):\n";
foreach ($users as $user) {
    echo "- #{$user->id} {$user->email} ({$user->name}) role_id={$user->role_id}\n";
}

if ($users->isEmpty()) {
    echo "(none)\n";
}
