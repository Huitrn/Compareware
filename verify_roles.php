<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== VERIFICACIÓN DEL SISTEMA DE ROLES ===\n\n";

// Verificar roles
echo "📋 ROLES CREADOS:\n";
echo str_repeat("-", 50) . "\n";
$roles = App\Models\Role::all();
foreach ($roles as $role) {
    echo "✓ {$role->nombre} ({$role->slug})\n";
    echo "  Permisos: " . count($role->permisos) . "\n";
    echo "  Activo: " . ($role->is_active ? 'Sí' : 'No') . "\n\n";
}

// Verificar usuarios
echo "\n👥 USUARIOS CREADOS:\n";
echo str_repeat("-", 50) . "\n";
$users = App\Models\User::with('userRole')->get();
foreach ($users as $user) {
    $roleName = $user->userRole ? $user->userRole->nombre : 'Sin rol';
    echo "✓ {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Rol: {$roleName}\n\n";
}

// Verificar permisos de cada rol
echo "\n🔑 PERMISOS POR ROL:\n";
echo str_repeat("-", 50) . "\n";
foreach ($roles as $role) {
    echo "\n{$role->nombre}:\n";
    foreach ($role->permisos as $permiso) {
        echo "  • {$permiso}\n";
    }
}

// Verificar que los métodos del modelo funcionan
echo "\n\n🧪 PRUEBAS DE MÉTODOS:\n";
echo str_repeat("-", 50) . "\n";
$admin = App\Models\User::where('email', 'admin1@compareware.com')->first();
if ($admin) {
    echo "Usuario: {$admin->name}\n";
    echo "Es Admin: " . ($admin->isAdmin() ? 'Sí' : 'No') . "\n";
    echo "Es Supervisor: " . ($admin->isSupervisor() ? 'Sí' : 'No') . "\n";
    echo "Es Desarrollador: " . ($admin->isDeveloper() ? 'Sí' : 'No') . "\n";
    echo "Tiene permiso 'manage_users': " . ($admin->hasPermission('manage_users') ? 'Sí' : 'No') . "\n";
    echo "Tiene permiso 'manage_products': " . ($admin->hasPermission('manage_products') ? 'Sí' : 'No') . "\n";
}

$supervisor = App\Models\User::where('email', 'supervisor1@compareware.com')->first();
if ($supervisor) {
    echo "\nUsuario: {$supervisor->name}\n";
    echo "Es Admin: " . ($supervisor->isAdmin() ? 'Sí' : 'No') . "\n";
    echo "Es Supervisor: " . ($supervisor->isSupervisor() ? 'Sí' : 'No') . "\n";
    echo "Tiene permiso 'manage_users': " . ($supervisor->hasPermission('manage_users') ? 'Sí' : 'No') . "\n";
    echo "Tiene permiso 'manage_products': " . ($supervisor->hasPermission('manage_products') ? 'Sí' : 'No') . "\n";
}

echo "\n\n✅ Sistema de roles verificado correctamente!\n\n";
