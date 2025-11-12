<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemChange;
use App\Events\SystemChangeEvent;
use App\Models\User;

class TestAdminNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification {--sync : Enviar inmediatamente sin cola}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía una notificación de prueba a los administradores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Iniciando prueba de notificaciones...');
        $this->newLine();

        // Verificar que existen administradores
        $admins = User::whereHas('userRole', function ($query) {
            $query->where('nombre', 'Admin');
        })->get();

        if ($admins->isEmpty()) {
            $this->error('❌ No se encontraron usuarios administradores en el sistema.');
            $this->info('💡 Asegúrate de tener al menos un usuario con rol "Admin"');
            return 1;
        }

        $this->info("✅ Se encontraron {$admins->count()} administrador(es):");
        foreach ($admins as $admin) {
            $this->line("   • {$admin->name} ({$admin->email})");
        }
        $this->newLine();

        // Crear un cambio de prueba
        $this->info('📝 Creando registro de cambio de prueba...');
        
        $change = SystemChange::create([
            'user_id' => auth()->id() ?? $admins->first()->id,
            'action_type' => 'create',
            'model_type' => 'Periferico',
            'model_id' => 999,
            'description' => '🧪 PRUEBA: Se creó un periférico de prueba - Logitech G502 HERO',
            'changes' => [
                'nombre' => 'Logitech G502 HERO',
                'marca' => 'Logitech',
                'precio' => 1299.00,
                'categoria' => 'Mouse'
            ],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => 'Test Command',
            'notified' => false,
        ]);

        $this->info("✅ Cambio registrado con ID: {$change->id}");
        $this->newLine();

        if ($this->option('sync')) {
            // Enviar de forma síncrona (inmediata)
            $this->info('📧 Enviando notificaciones de forma inmediata (síncrona)...');
            
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new \App\Notifications\AdminChangeNotification($change));
                    $this->info("   ✓ Notificación enviada a: {$admin->email}");
                } catch (\Exception $e) {
                    $this->error("   ✗ Error al enviar a {$admin->email}: {$e->getMessage()}");
                }
            }
            
            $change->markAsNotified();
            
        } else {
            // Enviar mediante evento (asíncrono con cola)
            $this->info('📤 Disparando evento para envío asíncrono...');
            event(new SystemChangeEvent($change));
            $this->info('✅ Evento disparado. Las notificaciones se enviarán mediante la cola.');
            $this->newLine();
            $this->warn('⚠️  Asegúrate de que el worker de colas esté ejecutándose:');
            $this->line('   php artisan queue:work');
        }

        $this->newLine();
        $this->info('🎉 Prueba completada exitosamente!');
        $this->newLine();
        $this->info('📋 Próximos pasos:');
        $this->line('   1. Revisa tu email (puede tardar unos segundos)');
        $this->line('   2. Si no llega, revisa: storage/logs/laravel.log');
        $this->line('   3. Verifica la tabla jobs: SELECT * FROM jobs;');
        $this->line('   4. Verifica la tabla system_changes');
        
        return 0;
    }
}
