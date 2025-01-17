<?php

namespace Asikam\Speedex;

use Illuminate\Support\ServiceProvider;

class SpeedexServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register package services.
        app()->config["filesystems.disks.speedex"] = [
            'driver' => 'local',
            'root' => storage_path('app/private/speedex'),
            'serve' => true,
            'throw' => false,
        ];
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/speedex.php' => config_path('speedex.php'),
        ]);
    }
}
