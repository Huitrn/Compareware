<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Crear usuario administrador específico
        $adminEmail = 'huitrnadmini@mail.com';
        
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $adminEmail)->first();
        
        if ($existingUser) {
            // Si existe, actualizar los datos
            $existingUser->update([
                'name' => 'Huitrnadmin',
                'password' => Hash::make('Huitrn03!@?'),
                'role' => 'admin'
            ]);
            
            echo "Usuario administrador actualizado:\n";
            echo "- Nombre: Huitrnadmin\n";
            echo "- Email: huitrnadmini@mail.com\n";
            echo "- Contraseña: Huitrn03!@?\n";
            echo "- Rol: admin\n";
        } else {
            // Si no existe, crear nuevo usuario
            $user = User::create([
                'name' => 'Huitrnadmin',
                'email' => $adminEmail,
                'password' => Hash::make('Huitrn03!@?'),
                'role' => 'admin'
            ]);
            
            echo "Usuario administrador creado exitosamente:\n";
            echo "- ID: {$user->id}\n";
            echo "- Nombre: {$user->name}\n";
            echo "- Email: {$user->email}\n";
            echo "- Contraseña: Huitrn03!@?\n";
            echo "- Rol: {$user->role}\n";
        }
    }
}