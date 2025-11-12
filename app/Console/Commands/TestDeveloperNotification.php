<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\SystemChange;
use App\Events\SystemChangeEvent;
use App\Notifications\AdminChangeNotification;

class TestDeveloperNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:developer-notification {--sync : Enviar de forma síncrona sin queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el sistema de notificaciones de cambios de desarrollador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 Iniciando prueba de notificación de desarrollador...');
        $this->newLine();

        // 1. Buscar un desarrollador
        $developer = User::whereHas('userRole', function ($query) {
            $query->whereIn('nombre', ['Desarrollador', 'Developer']);
        })->first();

        if (!$developer) {
            $this->error('❌ No se encontró ningún usuario con rol de Desarrollador.');
            return 1;
        }

        $this->info("✅ Desarrollador encontrado: {$developer->name} (ID: {$developer->id})");

        // 2. Buscar administradores
        $admins = User::whereHas('userRole', function ($query) {
            $query->whereIn('nombre', ['Admin', 'Administrador']);
        })->get();

        if ($admins->isEmpty()) {
            $this->error('❌ No se encontraron administradores para notificar.');
            return 1;
        }

        $this->info("✅ Administradores encontrados: {$admins->count()}");
        foreach ($admins as $admin) {
            $this->line("   - {$admin->name} ({$admin->email})");
        }
        $this->newLine();

        // 3. Crear un registro de cambio de prueba
        $this->info('📝 Creando registro de cambio...');
        
        $systemChange = SystemChange::create([
            'user_id' => $developer->id,
            'action_type' => 'clear_cache',
            'model_type' => 'Cache',
            'model_id' => null, // Cambiar a null ya que no aplica para caché
            'description' => 'Prueba de limpieza de caché desde comando de test',
            'changes' => [
                'type' => 'all',
                'message' => 'Toda la caché limpiada (PRUEBA)',
                'timestamp' => now()->toDateTimeString()
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Artisan Console Test',
            'notified' => false,
        ]);

        $this->info("✅ Registro creado con ID: {$systemChange->id}");
        $this->newLine();

        // 4. Enviar notificaciones
        $sync = $this->option('sync');
        
        if ($sync) {
            $this->info('📧 Enviando notificaciones de forma SÍNCRONA...');
            $this->newLine();
            
            foreach ($admins as $admin) {
                try {
                    $this->line("   Enviando a {$admin->name} ({$admin->email})...");
                    $admin->notify(new AdminChangeNotification($systemChange));
                    $this->info("   ✅ Enviado correctamente");
                } catch (\Exception $e) {
                    $this->error("   ❌ Error: {$e->getMessage()}");
                }
            }
            
            $systemChange->markAsNotified();
            
        } else {
            $this->info('🚀 Disparando evento para notificación asíncrona (queue)...');
            event(new SystemChangeEvent($systemChange));
            $this->info('✅ Evento disparado. Las notificaciones se procesarán en segundo plano.');
            $this->newLine();
            $this->warn('⚠️  Para procesar la cola, ejecuta: php artisan queue:work');
        }

        $this->newLine();
        $this->info('🎉 Prueba completada exitosamente!');
        $this->newLine();
        
        // Mostrar información útil
        $this->info('📊 Información útil:');
        $this->line("   - Configuración de correo: " . config('mail.mailers.smtp.host'));
        $this->line("   - Usuario de correo: " . config('mail.mailers.smtp.username'));
        $this->line("   - Puerto: " . config('mail.mailers.smtp.port'));
        $this->line("   - Encriptación: " . config('mail.mailers.smtp.encryption'));
        $this->newLine();

        return 0;
    }
}
