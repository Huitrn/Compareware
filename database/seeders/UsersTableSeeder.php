<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los roles
        $adminRole = Role::where('slug', 'administrador')->first();
        $supervisorRole = Role::where('slug', 'supervisor')->first();
        $developerRole = Role::where('slug', 'desarrollador')->first();

        // Crear 2 Administradores
        User::create([
            'name' => 'Eduardo Huitrón',
            'email' => 'ehuitron03@gmail.com',
            'password' => Hash::make('Huitrn03!@?'),
            'role_id' => $adminRole->id
        ]);

        User::create([
            'name' => 'Admin Secundario',
            'email' => 'admin2@compareware.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id
        ]);

        // Crear 2 Supervisores
        User::create([
            'name' => 'Supervisor de Productos',
            'email' => 'supervisor1@compareware.com',
            'password' => Hash::make('supervisor123'),
            'role_id' => $supervisorRole->id
        ]);

        User::create([
            'name' => 'Supervisor de Usuarios',
            'email' => 'supervisor2@compareware.com',
            'password' => Hash::make('supervisor123'),
            'role_id' => $supervisorRole->id
        ]);

        // Crear 2 Desarrolladores
        User::create([
            'name' => 'Desarrollador Backend',
            'email' => 'dev1@compareware.com',
            'password' => Hash::make('developer123'),
            'role_id' => $developerRole->id
        ]);

        User::create([
            'name' => 'Desarrollador Frontend',
            'email' => 'dev2@compareware.com',
            'password' => Hash::make('developer123'),
            'role_id' => $developerRole->id
        ]);
    }
}
