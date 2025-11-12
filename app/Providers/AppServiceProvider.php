<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\Periferico;
use App\Observers\PerifericoObserver;
use App\Events\SystemChangeEvent;
use App\Listeners\SystemChangeListener;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observers
        Periferico::observe(PerifericoObserver::class);

        // Registrar eventos y listeners
        Event::listen(
            SystemChangeEvent::class,
            SystemChangeListener::class,
        );
    }
}
