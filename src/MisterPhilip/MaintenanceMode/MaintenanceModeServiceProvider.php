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

    /**
     * Bootstrap our application events.
     *
     * @return void
     */
	public function boot()
	{
		$this->registerConfig();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

	}

    /**
     * Register our config file
     *
     * @return void
     */
    protected function registerConfig()
    {
        // Setup the paths
        $userConfigPath = app()->configPath() . '/packages/misterphilip/maintenancemode/config.php';
        $defaultConfigPath = __DIR__ .'/../../config/config.php';

        // Grab the default config
        $config = $this->app['files']->getRequire($defaultConfigPath);

        // Check if the user-configuration exists
        if(file_exists($userConfigPath))
        {
            // User has config, let's merge them properly
            $userConfig = $this->app['files']->getRequire($userConfigPath);
            $config = array_replace_recursive($config, $userConfig);
        }

        // Set each of the items like ->package() previously did
        $this->app['config']->set('maintenancemode::config', $config);
        $this->app['view']->addNamespace('maintenancemode', __DIR__.'/../../views');
        $this->app['translator']->addNamespace('maintenancemode', __DIR__.'/../../lang');
    }
}