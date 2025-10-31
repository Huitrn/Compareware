<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        // Crear usuario de prueba si no existe
        if (!User::where('email', 'test@compareware.com')->exists()) {
            User::create([
                'name' => 'Usuario de Prueba',
                'email' => 'test@compareware.com',
                'password' => Hash::make('Password123!'),
                'role' => 'user'
            ]);
        }

        // Crear admin de prueba si no existe
        if (!User::where('email', 'admin@compareware.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@compareware.com',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin'
            ]);
        }

        echo "Usuarios de prueba creados:\n";
        echo "- test@compareware.com / Password123!\n";
        echo "- admin@compareware.com / Admin123!\n";
    }
}