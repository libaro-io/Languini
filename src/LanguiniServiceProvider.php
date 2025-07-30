<?php

namespace LibaroIo\Languini;

use Illuminate\Support\ServiceProvider;

class LanguiniServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }
}