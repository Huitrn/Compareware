<?php

namespace App\Listeners;

use App\Events\SystemChangeEvent;
use App\Notifications\AdminChangeNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SystemChangeListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SystemChangeEvent $event): void
    {
        $systemChange = $event->systemChange;

        try {
            // Obtener todos los administradores
            $admins = User::whereHas('userRole', function ($query) {
                $query->where('nombre', 'Admin');
            })->get();

            // Enviar notificación a cada administrador
            foreach ($admins as $admin) {
                $admin->notify(new AdminChangeNotification($systemChange));
            }

            // Marcar como notificado
            $systemChange->markAsNotified();

            // Log de éxito
            Log::info('Notificaciones de cambio enviadas', [
                'change_id' => $systemChange->id,
                'admins_count' => $admins->count(),
            ]);

        } catch (\Exception $e) {
            // Log de error
            Log::error('Error al enviar notificaciones de cambio', [
                'change_id' => $systemChange->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
