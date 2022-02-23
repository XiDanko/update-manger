<?php

namespace XiDanko\UpdateManager;

use Illuminate\Support\ServiceProvider;

class UpdateManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/update-manager.php', 'update-manager');
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([__DIR__ . '/../config/update-manager.php' => config_path('update-manager.php')], 'update-manager-config');
    }
}
