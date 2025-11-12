<?php

namespace App\Notifications;

use App\Models\SystemChange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $systemChange;

    /**
     * Create a new notification instance.
     */
    public function __construct(SystemChange $systemChange)
    {
        $this->systemChange = $systemChange;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $change = $this->systemChange;
        $actionTypeSpanish = $this->getActionTypeSpanish($change->action_type);
        $userName = $change->user ? $change->user->name : 'Usuario desconocido';

        $mail = (new MailMessage)
            ->subject('🔔 Alerta: Cambio en el Sistema - ' . $change->model_type)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Se ha detectado un cambio en el sistema CompareWare que requiere tu atención.')
            ->line('**Detalles del cambio:**')
            ->line('📝 **Acción:** ' . $actionTypeSpanish)
            ->line('📦 **Modelo:** ' . $change->model_type)
            ->line('👤 **Usuario:** ' . $userName)
            ->line('📋 **Descripción:** ' . $change->description)
            ->line('🕒 **Fecha:** ' . $change->created_at->format('d/m/Y H:i:s'))
            ->line('🌐 **IP:** ' . $change->ip_address);

        // Si hay detalles de cambios, agregarlos
        if ($change->changes && !empty($change->changes)) {
            $mail->line('**Detalles de los cambios:**');
            foreach ($change->changes as $key => $value) {
                if (is_array($value) && isset($value['old'], $value['new'])) {
                    $mail->line("• **{$key}:** De '{$value['old']}' a '{$value['new']}'");
                }
            }
        }

        $mail->action('Ver Panel de Administración', url('/admin'))
            ->line('Este es un mensaje automático del sistema de monitoreo de CompareWare.')
            ->line('Si no reconoces este cambio, por favor revisa el sistema de inmediato.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'change_id' => $this->systemChange->id,
            'action_type' => $this->systemChange->action_type,
            'model_type' => $this->systemChange->model_type,
            'description' => $this->systemChange->description,
        ];
    }

    /**
     * Traducir tipo de acción al español
     */
    private function getActionTypeSpanish(string $actionType): string
    {
        $translations = [
            'create' => 'Creación',
            'update' => 'Actualización',
            'delete' => 'Eliminación',
        ];

        return $translations[$actionType] ?? ucfirst($actionType);
    }
}
