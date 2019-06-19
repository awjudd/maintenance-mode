<?php

namespace MisterPhilip\MaintenanceMode\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\IpUtils;

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
     * The language path
     *
     * @var string
     */
    protected $language;

    /**
     * The prefix for the view variables
     *
     * @var string
     */
    protected $prefix;

    /**
     * If the information should be injected into all the views
     *
     * @var bool
     */
    protected $inject = false;

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
        $this->inject = $this->app['config']->get('maintenancemode.inject.globally', true);
        $this->prefix = $this->app['config']->get('maintenancemode.inject.prefix', 'MaintenanceMode');
        $this->language = $this->app['config']->get('maintenancemode.language-path', 'maintenancemode::defaults');

        // Setup value array
        $info = [
            'Enabled'     => false,
            'Timestamp'   => time(),
            'Message'     => $this->app['translator']->get($this->language . '.message'),
            'View'        => null,
            'Retry'       => 60,
        ];

        // Are we down?
        if ($this->app->isDownForMaintenance()) {
            // Yes. :(
            Carbon::setLocale(App::getLocale());

            $info['Enabled'] = true;

            $data = ["message" => null, "view" => $this->app['config']->get('maintenancemode.view'), "retry" => null, "time" => Carbon::now()->getTimestamp()];

            $data = array_merge($data, json_decode(file_get_contents($this->app->storagePath().'/framework/down'), true));

            // Update the array with data from down file
            $info['Timestamp'] = Carbon::createFromTimestamp($data['time']);

            if (isset($data['message']) && $data['message']) {
                $info['Message'] = $data['message'];
            }
            if (isset($data['view']) && $data['view']) {
                $info['View'] = $data['view'];
            }
            if (isset($data['retry']) && intval($data['retry'], 10) !== 0) {
                $info['Retry'] = intval($data['retry'], 10);
            }

            // Inject the information into the views before the exception
            $this->injectIntoViews($info);

            if (!$this->isExempt($data, $request)) {
                // The user isn't exempt, so show them the error page
                throw new MaintenanceModeException($data['time'], $data['retry'], $data['message'], $data['view']);
            }
        } else {
            // Inject the default information into the views
            $this->injectIntoViews($info);
        }

        return $next($request);
    }
    
    /**
     * Inject the prefixed data into the views
     *
     * @param $info
     * @return null
     */
    protected function injectIntoViews($info)
    {
        if ($this->inject) {
            // Inject the information globally (to prevent the need of isset)
            foreach ($info as $key => $value) {
                $this->app['view']->share($this->prefix . $key, $value);
            }
        }
    }

    /**
     * Check if a user is exempt from the maintenance mode page
     *
     * @param $data
     * @param $request
     *
     * @return bool
     * @throws ExemptionDoesNotExist
     * @throws InvalidExemption
     */
    protected function isExempt($data, $request)
    {
        // Grab all of the exemption classes to create/execute against
        $exemptions = $this->app['config']->get('maintenancemode.exemptions', []);
        foreach ($exemptions as $className) {
            if (class_exists($className)) {
                $exemption = new $className($this->app);
                if ($exemption instanceof MaintenanceModeExemption) {
                    // Run the exemption check
                    if ($exemption->isExempt()) {
                        return true;
                    }
                } else {
                    // Class doesn't match what we're looking for
                    throw new InvalidExemption($this->app['translator']->get($this->language . '.exceptions.invalid', ['class' => $className]));
                }
            } else {
                // Where's Waldo?
                throw new ExemptionDoesNotExist($this->app['translator']->get($this->language . '.exceptions.missing', ['class' => $className]));
            }
        }

        // Check for IP via the "allow" option
        if (isset($data['allowed']) && IpUtils::checkIp($request->ip(), (array) $data['allowed'])) {
            return true;
        }

        return false;
    }
}
