<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Periferico;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class PerifericoPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver cualquier periférico.
     */
    public function viewAny(User $user): bool
    {
        // Todos los usuarios autenticados pueden ver periféricos
        return true;
    }

    /**
     * Determinar si el usuario puede ver un periférico específico.
     */
    public function view(User $user, Periferico $periferico): bool
    {
        // Los usuarios pueden ver periféricos activos
        // Los admins pueden ver todos
        if ($user->isAdmin()) {
            return true;
        }

        return $periferico->is_active ?? true;
    }

    /**
     * Determinar si el usuario puede crear periféricos.
     */
    public function create(User $user): bool
    {
        $canCreate = $user->isAdmin();
        
        // Log de intento de creación
        if (!$canCreate) {
            Log::channel('security')->warning('Unauthorized creation attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'action' => 'create_periferico',
                'ip' => request()->ip()
            ]);
        }

        return $canCreate;
    }

    /**
     * Determinar si el usuario puede actualizar el periférico.
     */
    public function update(User $user, Periferico $periferico): bool
    {
        $canUpdate = false;

        if ($user->isAdmin()) {
            $canUpdate = true;
        } elseif (method_exists($periferico, 'created_by') && $periferico->created_by === $user->id) {
            // El creador puede editar sus propios periféricos
            $canUpdate = true;
        }

        // Log de intento de actualización
        if (!$canUpdate) {
            Log::channel('security')->warning('Unauthorized update attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'periferico_id' => $periferico->id,
                'action' => 'update_periferico',
                'ip' => request()->ip()
            ]);
        }

        return $canUpdate;
    }

    /**
     * Determinar si el usuario puede eliminar el periférico.
     */
    public function delete(User $user, Periferico $periferico): bool
    {
        $canDelete = $user->isAdmin();

        // Log de intento de eliminación
        if (!$canDelete) {
            Log::channel('security')->warning('Unauthorized deletion attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'periferico_id' => $periferico->id,
                'action' => 'delete_periferico',
                'ip' => request()->ip()
            ]);
        }

        return $canDelete;
    }

    /**
     * Determinar si el usuario puede restaurar el periférico.
     */
    public function restore(User $user, Periferico $periferico): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determinar si el usuario puede eliminar permanentemente el periférico.
     */
    public function forceDelete(User $user, Periferico $periferico): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determinar si el usuario puede gestionar las especificaciones.
     */
    public function manageSpecifications(User $user, Periferico $periferico): bool
    {
        return $this->update($user, $periferico);
    }

    /**
     * Determinar si el usuario puede ver datos sensibles del periférico.
     */
    public function viewSensitiveData(User $user, Periferico $periferico): bool
    {
        return $user->isAdmin();
    }
}