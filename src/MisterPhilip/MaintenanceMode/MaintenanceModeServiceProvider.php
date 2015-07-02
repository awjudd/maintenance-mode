<?php namespace MisterPhilip\MaintenanceMode;

use Illuminate\Support\ServiceProvider;

class MaintenanceModeServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    const BASE_PATH = __DIR__ . '/../../';

    /**
     * Bootstrap our application events.
     *
     * @return void
     */
	public function boot()
	{
        // Register our resources
        $this->loadViews();
        $this->loadTranslations();
		$this->loadConfig();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->mergeConfigFrom(
            self::BASE_PATH . 'config/maintenancemode.php', 'maintenancemode'
        );
	}

    /**
     * Register our view files
     *
     * @return void
     */
    protected function loadViews()
    {
        $this->loadViewsFrom(self::BASE_PATH . 'views', 'maintenancemode');

        $this->publishes([
            self::BASE_PATH . 'views' => base_path('resources/views/vendor/maintenancemode'),
        ], 'views');
    }

    /**
     * Register our translations
     *
     * @return void
     */
    protected function loadTranslations()
    {
        $this->loadTranslationsFrom(self::BASE_PATH . 'lang', 'maintenancemode');
    }

    /**
     * Register our config file
     *
     * @return void
     */
    protected function loadConfig()
    {
        $this->publishes([
            self::BASE_PATH . 'config/maintenancemode.php' => config_path('maintenancemode.php'),
        ], 'config');
    }
}