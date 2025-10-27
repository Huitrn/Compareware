<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver cualquier usuario.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determinar si el usuario puede ver un usuario específico.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        // Los usuarios pueden ver su propio perfil
        // Los admins pueden ver cualquier usuario
        return $authUser->id === $targetUser->id || $authUser->isAdmin();
    }

    /**
     * Determinar si el usuario puede crear usuarios.
     */
    public function create(User $user): bool
    {
        // Solo admins pueden crear usuarios directamente
        // El registro público se maneja por separado
        return $user->isAdmin();
    }

    /**
     * Determinar si el usuario puede actualizar otro usuario.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        // Los usuarios pueden actualizar su propio perfil (con restricciones)
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Los admins pueden actualizar cualquier usuario
        return $authUser->isAdmin();
    }

    /**
     * Determinar si el usuario puede eliminar otro usuario.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        // No se puede eliminar a sí mismo
        if ($authUser->id === $targetUser->id) {
            Log::channel('security')->warning('Self-deletion attempt', [
                'user_id' => $authUser->id,
                'ip' => request()->ip()
            ]);
            return false;
        }

        // Solo admins pueden eliminar usuarios
        return $authUser->isAdmin();
    }

    /**
     * Determinar si el usuario puede cambiar roles.
     */
    public function changeRole(User $authUser, User $targetUser): bool
    {
        // No se puede cambiar su propio rol
        if ($authUser->id === $targetUser->id) {
            Log::channel('security')->critical('Self role change attempt', [
                'user_id' => $authUser->id,
                'ip' => request()->ip()
            ]);
            return false;
        }

        // Solo admins pueden cambiar roles
        return $authUser->isAdmin();
    }

    /**
     * Determinar si el usuario puede ver datos sensibles.
     */
    public function viewSensitiveData(User $authUser, User $targetUser): bool
    {
        // Los usuarios pueden ver sus propios datos sensibles
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Los admins pueden ver datos sensibles de otros usuarios
        return $authUser->isAdmin();
    }

    /**
     * Determinar si el usuario puede resetear contraseñas.
     */
    public function resetPassword(User $authUser, User $targetUser): bool
    {
        // Los usuarios pueden resetear su propia contraseña
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Los admins pueden resetear contraseñas
        return $authUser->isAdmin();
    }

    /**
     * Determinar si el usuario puede ver el historial de actividad.
     */
    public function viewActivityLog(User $authUser, User $targetUser): bool
    {
        return $authUser->isAdmin();
    }

    /**
     * Determinar si el usuario puede impersonar a otro usuario.
     */
    public function impersonate(User $authUser, User $targetUser): bool
    {
        // No se puede impersonar a sí mismo
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // No se puede impersonar a otros admins
        if ($targetUser->isAdmin()) {
            return false;
        }

        // Solo admins pueden impersonar
        return $authUser->isAdmin();
    }
}