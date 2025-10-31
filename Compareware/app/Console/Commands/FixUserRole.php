<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-role {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix user role to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("🔍 Buscando usuario con email: {$email}");
        
        // Buscar usuario
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado");
            return;
        }
        
        $this->info("👤 Usuario encontrado:");
        $this->info("   ID: {$user->id}");
        $this->info("   Nombre: {$user->name}");
        $this->info("   Email: {$user->email}");
        $this->info("   Rol actual: {$user->role}");
        
        // Actualizar rol
        $user->role = 'admin';
        $saved = $user->save();
        
        if ($saved) {
            $this->info("✅ Rol actualizado a 'admin'");
            
            // Verificar el cambio
            $user->refresh();
            $this->info("🔄 Verificación - Nuevo rol: {$user->role}");
        } else {
            $this->error("❌ Error al guardar el usuario");
        }
        
        // También intentar con query builder
        $this->info("🔧 Actualizando también con Query Builder...");
        $affected = DB::table('users')
            ->where('email', $email)
            ->update(['role' => 'admin']);
            
        $this->info("📝 Filas afectadas: {$affected}");
        
        // Verificar en la base de datos
        $dbRole = DB::table('users')
            ->where('email', $email)
            ->value('role');
            
        $this->info("🗄️ Rol en base de datos: {$dbRole}");
    }
}
