<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugUserRole extends Command
{
    protected $signature = 'debug:user-role {email}';
    protected $description = 'Debug user role issues';

    public function handle()
    {
        $email = $this->argument('email');
        
        // 1. Ver información de la tabla
        $this->info('=== INFORMACIÓN DE LA TABLA USERS ===');
        $columns = DB::select("SELECT column_name, data_type, is_nullable, column_default 
                              FROM information_schema.columns 
                              WHERE table_name = 'users' AND column_name = 'role'");
        foreach($columns as $col) {
            $this->info("Columna: {$col->column_name}");
            $this->info("Tipo: {$col->data_type}");
            $this->info("Nulo: {$col->is_nullable}");
            $this->info("Default: " . ($col->column_default ?? 'NULL'));
        }
        
        // 2. Ver datos actuales del usuario
        $this->info("\n=== DATOS ACTUALES DEL USUARIO ===");
        $user = DB::select("SELECT id, name, email, role FROM users WHERE email = ?", [$email]);
        if ($user) {
            $u = $user[0];
            $this->info("ID: {$u->id}");
            $this->info("Nombre: {$u->name}");
            $this->info("Email: {$u->email}");
            $this->info("Rol: '{$u->role}' (length: " . strlen($u->role) . ")");
            $this->info("Rol hex: " . bin2hex($u->role));
        }
        
        // 3. Intentar UPDATE directo
        $this->info("\n=== INTENTANDO UPDATE DIRECTO ===");
        $affected = DB::update("UPDATE users SET role = ? WHERE email = ?", ['admin', $email]);
        $this->info("Filas afectadas: {$affected}");
        
        // 4. Verificar después del update
        $this->info("\n=== VERIFICACIÓN POST-UPDATE ===");
        $userAfter = DB::select("SELECT role FROM users WHERE email = ?", [$email]);
        if ($userAfter) {
            $roleAfter = $userAfter[0]->role;
            $this->info("Rol después del update: '{$roleAfter}'");
            $this->info("Rol hex después: " . bin2hex($roleAfter));
        }
    }
}