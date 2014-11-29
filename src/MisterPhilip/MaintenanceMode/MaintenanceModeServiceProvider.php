<?php namespace MisterPhilip\MaintenanceMode;

use Illuminate\Support\ServiceProvider;
use MisterPhilip\MaintenanceMode\Console\Commands\StartMaintenanceCommand;

class MaintenanceModeServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot()
	{
		$this->package('misterphilip/maintenancemode');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerCommands();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['maintenancemode'];
	}

	/*
	 * Register our command(s)
	 */
	private function registerCommands()
	{
		$this->app['command.maintenancemode.down'] = $this->app->share(function ($app)
		{
			return new StartMaintenanceCommand($app);
		});
		$this->commands('command.maintenancemode.down');
	}
}