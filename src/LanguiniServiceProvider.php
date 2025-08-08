<?php

namespace LibaroIo\Languini;

use Illuminate\Support\ServiceProvider;

class LanguiniServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/languini.php', 'languini'
        );

        $this->publishes([
            __DIR__.'/config/languini.php' => config_path('languini.php'),
        ], 'languini');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'languini');

    }
}
