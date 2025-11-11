<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::select('id', 'name', 'email')->get();

echo "Usuarios registrados:\n\n";
foreach ($users as $user) {
    echo "ID: {$user->id} | Email: {$user->email} | Nombre: {$user->name}\n";
}
echo "\nTotal: " . $users->count() . " usuarios\n";
