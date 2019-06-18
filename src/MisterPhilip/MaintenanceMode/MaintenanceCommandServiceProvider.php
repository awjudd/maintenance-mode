<?php

namespace MisterPhilip\MaintenanceMode;

use Illuminate\Support\ServiceProvider;
use MisterPhilip\MaintenanceMode\Console\Commands\EndMaintenanceCommand;
use MisterPhilip\MaintenanceMode\Console\Commands\StartMaintenanceCommand;

class MaintenanceCommandServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.down', function ($app) {
            return new StartMaintenanceCommand();
        });


        $this->app->singleton('command.up', function ($app) {
            return new EndMaintenanceCommand();
        });
        $this->commands(['command.down', 'command.up']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.down', 'command.up'];
    }
}
