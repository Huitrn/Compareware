<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Rol Administrador
        Role::create([
            'nombre' => 'Administrador',
            'slug' => 'administrador',
            'descripcion' => 'Acceso total al sistema. Puede gestionar usuarios, productos, configuración y todos los aspectos del sistema.',
            'permisos' => [
                'manage_users',
                'create_users',
                'edit_users',
                'delete_users',
                'manage_roles',
                'manage_products',
                'create_products',
                'edit_products',
                'delete_products',
                'approve_products',
                'manage_categories',
                'manage_brands',
                'manage_config',
                'view_logs',
                'view_reports',
                'manage_cache',
                'test_apis',
                'view_config',
                'edit_config',
                'manage_chatbot',
                'view_statistics'
            ],
            'is_active' => true
        ]);

        // Rol Supervisor
        Role::create([
            'nombre' => 'Supervisor',
            'slug' => 'supervisor',
            'descripcion' => 'Gestiona productos y usuarios básicos. Puede aprobar productos, ver usuarios y generar reportes.',
            'permisos' => [
                'view_users',
                'edit_users',
                'manage_products',
                'create_products',
                'edit_products',
                'approve_products',
                'manage_categories',
                'manage_brands',
                'view_reports',
                'view_statistics',
                'manage_chatbot'
            ],
            'is_active' => true
        ]);

        // Rol Desarrollador
        Role::create([
            'nombre' => 'Desarrollador',
            'slug' => 'desarrollador',
            'descripcion' => 'Acceso a herramientas de desarrollo. Puede ver logs, gestionar caché, probar APIs y ver configuración.',
            'permisos' => [
                'view_logs',
                'manage_cache',
                'test_apis',
                'view_config',
                'view_users',
                'view_products',
                'view_reports',
                'manage_chatbot'
            ],
            'is_active' => true
        ]);
    }
}
