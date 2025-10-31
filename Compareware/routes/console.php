<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('verify:admin', function () {
    $user = \App\Models\User::where('email', 'huitrnadmini@mail.com')->first();
    
    if ($user) {
        $this->info("✅ Usuario administrador verificado:");
        $this->line("- ID: {$user->id}");
        $this->line("- Nombre: {$user->name}");
        $this->line("- Email: {$user->email}");
        $this->line("- Rol: {$user->role}");
        $this->line("- Creado: {$user->created_at}");
        
        // Verificar que la contraseña funciona
        if (\Hash::check('Huitrn03!@?', $user->password)) {
            $this->info("🔐 Contraseña verificada correctamente");
        } else {
            $this->error("❌ Error en la contraseña");
        }
    } else {
        $this->error("❌ Usuario no encontrado");
    }
})->describe('Verificar usuario administrador');
