<?php namespace MisterPhilip\MaintenanceMode\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Response;

use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as LaravelMaintenanceMode;

use Illuminate\Support\Facades\App;
use MisterPhilip\MaintenanceMode\Exceptions\MaintenanceModeException;
use MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption;
use MisterPhilip\MaintenanceMode\Exceptions\InvalidExemption;
use MisterPhilip\MaintenanceMode\Exceptions\ExemptionDoesNotExist;

/**
 * Class CheckForMaintenanceMode
 *
 * @package MisterPhilip\MaintenanceMode
 */
class CheckForMaintenanceMode extends LaravelMaintenanceMode
{
    /**
     * Handle the request
     *
     * @param \Illuminate\Http\Request $request
     * @param  \Closure                $next
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
            $prefix . 'View'        => null,
            $prefix . 'Retry'       => 60,
        ];

        // Are we down?
        if($this->app->isDownForMaintenance())
        {
            // Yes. :(
            Carbon::setLocale(App::getLocale());

            $info[$prefix.'Enabled'] = true;

            $data = json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true);

            // Update the array with data from down file
            $info[$prefix . 'Timestamp'] = Carbon::createFromTimestamp($data['time']);

            if(isset($data['message']) && $data['message'])
            {
                $info[$prefix . 'Message'] = $data['message'];
            }
            if(isset($data['view']) && $data['view'])
            {
                $info[$prefix . 'View'] = $data['view'];
            }
            if(isset($data['retry']) && intval($data['retry'], 10) !== 0)
            {
                $info[$prefix . 'Retry'] = intval($data['retry'], 10);
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
                throw new MaintenanceModeException($data['time'], $data['retry'], $data['message'],  $data['view']);
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