<?php namespace MisterPhilip\MaintenanceMode\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;

use MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption;
use MisterPhilip\MaintenanceMode\Exceptions\InvalidExemption;
use MisterPhilip\MaintenanceMode\Exceptions\ExemptionDoesNotExist;

/**
 * Class CheckForMaintenanceMode
 *
 * @package MisterPhilip\MaintenanceMode
 */
class CheckForMaintenanceMode
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new filter instance.
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle the request
     *
     * @param \Illuminate\Http\Request $request
     * @param callable                 $next
     * @return Response
     * @throws ExemptionDoesNotExist
     * @throws InvalidExemption
     */
    public function handle($request, Closure $next)
    {
        // Grab our configs
        $injectGlobally = $this->app['config']->get('maintenancemode.inject.globally', true);
        $prefix = $this->app['config']->get('maintenancemode.inject.prefix', 'MaintenanceMode');
        $lang = $this->app['config']->get('maintenancemode.language-path', 'maintenancemode::defaults');

        // Setup value array
        $info = [
            $prefix . 'Enabled'     => false,
            $prefix . 'Timestamp'   => time(),
            $prefix . 'Message'     => $this->app['translator']->get($lang . '.message'),
            $prefix . 'View'        => '',
            $prefix . 'Retry'       => null,
        ];

        // Are we down?
        if($this->app->isDownForMaintenance())
        {
            // Yes. :(
            $info[$prefix.'Enabled'] = true;

            $data = json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true);

            // Update the array with data from down file
            $info[$prefix . 'Timestamp'] = $data['time'];
            $info[$prefix . 'Message'] = $data['message'];
            $info[$prefix . 'View'] = $data['view'];
            $info[$prefix . 'Retry'] = $data['retry'];

            if($injectGlobally)
            {
                // Inject the information globally
                foreach($info as $key => $value)
                {
                    $this->app['view']->share($key, $value);
                }
            }

            // Check to see if the user is exempt or not
            $isExempt = false;

            // Grab all of the exemption classes to create/execute against
            $exemptions = $this->app['config']->get('maintenancemode.exemptions', []);
            foreach($exemptions as $className)
            {
                if(class_exists($className))
                {
                    $exemption = new $className($this->app);
                    if($exemption instanceof MaintenanceModeExemption)
                    {
                        // Run the exemption check
                        if($exemption->isExempt())
                        {
                            $isExempt = true;
                            break;
                        }
                    }
                    else
                    {
                        // Class doesn't match what we're looking for
                        throw new InvalidExemption($this->app['translator']->get($lang . '.exceptions.invalid', ['class' => $className]));
                    }
                }
                else
                {
                    // Where's Waldo?
                    throw new ExemptionDoesNotExist($this->app['translator']->get($lang . '.exceptions.missing', ['class' => $className]));
                }
            }

            if(!$isExempt)
            {
                // Since the session isn't started... it'll throw an error
                $this->app['session']->start();

                throw new MaintenanceModeException($data['time'], $data['retry'], $data['message']);
            }
        }
        else
        {
            if($injectGlobally)
            {
                // Inject the information globally (to prevent the need of isset)
                foreach($info as $key => $value)
                {
                    $this->app['view']->share($key, $value);
                }
            }
        }
        return $next($request);
    }
}