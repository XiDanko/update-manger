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
        $this->publishes([__DIR__ . '/../config/update-manager.php' => config_path('update-manager.php')], 'update-manager-config');
        $this->publishes([__DIR__ . '/../database/migrations/2022_02_21_210710_create_updates_table.php' => database_path('migrations/2022_02_21_210710_create_updates_table.php')], 'update-manager-migration');
    }
}
