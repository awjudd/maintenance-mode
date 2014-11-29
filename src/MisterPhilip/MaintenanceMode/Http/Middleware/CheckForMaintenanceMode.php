<?php namespace MisterPhilip\MaintenanceMode\Http\Middleware;

use Closure, Config, File, Lang, View, Session;
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
        $injectGlobally = Config::get('maintenancemode::inject.globally', true);
        $prefix = Config::get('maintenancemode::inject.prefix', 'MaintenanceMode');
        $lang = Config::get('maintenancemode::language-path', 'maintenancemode::defaults.');

        // Setup value array
        $info = [
            $prefix . 'Enabled' => false,
            $prefix . 'Timestamp' => Carbon::now(),
            $prefix . 'Message' => Lang::get($lang . '.message'),
        ];

        // Are we down?
        if($this->app->isDownForMaintenance())
        {
            // Yes. :(
            $info[$prefix.'Enabled'] = true;

           $path = Config::get('app.manifest').'/down';
            if(File::exists($path))
            {
                // Grab the stored information
                $fileContents = File::get($path);
                if(preg_match('~([0-9]+)\|(.*)~', $fileContents, $matches))
                {
                    // And put it into our array, if it exists
                    $info[$prefix.'Timestamp'] = Carbon::createFromTimeStamp($matches[1]);
                    $info[$prefix.'Message'] = $matches[2];
                }
            }

            if($injectGlobally)
            {
                // Inject the information globally
                foreach($info as $key => $value)
                {
                    View::share($key, $value);
                }
            }

            // Check to see if the user is exempt or not
            $isExempt = false;

            // Grab all of the exemption classes to create/execute against
            $exemptions = Config::get('maintenancemode::exemptions', []);
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
                        throw new InvalidExemption(Lang::get($lang . '.exceptions.invalid', ['class' => $className]));
                    }
                }
                else
                {
                    // Where's Waldo?
                    throw new ExemptionDoesNotExist(Lang::get($lang . '.exceptions.missing', ['class' => $className]));
                }
            }

            if(!$isExempt)
            {
                // Since the session isn't started... it'll throw an error
                Session::start();

                // The user isn't exempt, let's show them the maintenance page!
                $view = Config::get('maintenancemode::view-page', 'maintenancemode::app-down');
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
                    View::share($key, $value);
                }
            }
        }

        return $next($request);
    }
}