<?php

namespace App\Observers;

use App\Models\Periferico;
use App\Models\SystemChange;
use App\Events\SystemChangeEvent;

class PerifericoObserver
{
    /**
     * Handle the Periferico "created" event.
     */
    public function created(Periferico $periferico): void
    {
        $change = SystemChange::logChange(
            'create',
            'Periferico',
            $periferico->id,
            "Se creó el periférico: {$periferico->nombre}",
            [
                'nombre' => $periferico->nombre,
                'marca' => $periferico->marca->nombre ?? 'Sin marca',
                'precio' => $periferico->precio,
            ]
        );

        event(new SystemChangeEvent($change));
    }

    /**
     * Handle the Periferico "updated" event.
     */
    public function updated(Periferico $periferico): void
    {
        $changes = [];
        $dirty = $periferico->getDirty();

        // Excluir updated_at de los cambios
        unset($dirty['updated_at']);

        if (!empty($dirty)) {
            foreach ($dirty as $key => $newValue) {
                $changes[$key] = [
                    'old' => $periferico->getOriginal($key),
                    'new' => $newValue,
                ];
            }

            $change = SystemChange::logChange(
                'update',
                'Periferico',
                $periferico->id,
                "Se actualizó el periférico: {$periferico->nombre}",
                $changes
            );

            event(new SystemChangeEvent($change));
        }
    }

    /**
     * Handle the Periferico "deleted" event.
     */
    public function deleted(Periferico $periferico): void
    {
        $change = SystemChange::logChange(
            'delete',
            'Periferico',
            $periferico->id,
            "Se eliminó el periférico: {$periferico->nombre}",
            [
                'nombre' => $periferico->nombre,
                'marca' => $periferico->marca->nombre ?? 'Sin marca',
            ]
        );

        event(new SystemChangeEvent($change));
    }

    /**
     * Handle the Periferico "restored" event.
     */
    public function restored(Periferico $periferico): void
    {
        $change = SystemChange::logChange(
            'restore',
            'Periferico',
            $periferico->id,
            "Se restauró el periférico: {$periferico->nombre}"
        );

        event(new SystemChangeEvent($change));
    }

    /**
     * Handle the Periferico "force deleted" event.
     */
    public function forceDeleted(Periferico $periferico): void
    {
        $change = SystemChange::logChange(
            'force_delete',
            'Periferico',
            $periferico->id,
            "Se eliminó permanentemente el periférico: {$periferico->nombre}"
        );

        event(new SystemChangeEvent($change));
    }
}
