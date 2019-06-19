<?php

namespace MisterPhilip\MaintenanceMode\Exemptions;

use Illuminate\Contracts\Foundation\Application;

/**
 * Class MaintenanceModeExemption
 *
 * @package MisterPhilip\MaintenanceMode
 */
abstract class MaintenanceModeExemption
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Check to see if the user is exempt from the maintenance page
     *
     * @return bool     True is the user should not see the exemption page
     */
    abstract public function isExempt();
}
