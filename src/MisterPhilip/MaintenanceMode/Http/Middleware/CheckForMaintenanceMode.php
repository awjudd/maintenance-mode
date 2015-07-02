<?php namespace MisterPhilip\MaintenanceMode\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Contracts\Foundation\Application;
use MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption;

use MisterPhilip\MaintenanceMode\Exceptions\InvalidExemption;
use MisterPhilip\MaintenanceMode\Exceptions\ExemptionDoesNotExist;

/**
 * Class CheckForMaintenanceMode
 *
 * @package MisterPhilip\MaintenanceMode
 */
class CheckForMaintenanceMode implements Middleware
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
            $prefix . 'Enabled' => false,
            $prefix . 'Timestamp' => Carbon::now(),
            $prefix . 'Message' => $this->app['translator']->get($lang . '.message'),
        ];

        // Are we down?
        if($this->app->isDownForMaintenance())
        {
            // Yes. :(
            $info[$prefix.'Enabled'] = true;

            $path = storage_path().'/framework/down';
            if($this->app['files']->exists($path))
            {
                // Grab the stored information
                $fileContents = $this->app['files']->get($path);
                if(preg_match('~([0-9]+)\|(.*)~', $fileContents, $matches))
                {
                    // And put it into our array, if it exists
                    $info[$prefix.'Timestamp'] = Carbon::createFromTimeStamp($matches[1]);
                    if(isset($matches[2]) && $matches[2] != '')
                    {
                        $info[$prefix.'Message'] = $matches[2];
                    }
                }
            }

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

                // The user isn't exempt, let's show them the maintenance page!
                $view = $this->app['config']->get('maintenancemode.view-page', 'maintenancemode::app-down');

                // $view = 'errors.503';
                return new Response(view($view, $info), 503);
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