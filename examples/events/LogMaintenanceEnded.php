<?php

namespace SampleApp\Listeners;

use Log;
use Carbon\Carbon;
use MisterPhilip\MaintenanceMode\Events\MaintenanceModeDisabled;

/**
 * Class LogMaintenanceEnded
 *
 * This class logs when the maintenance mode was disabled, and how long the application was down
 */
class LogMaintenanceEnded
{
    /**
     * Log when maintenance mode ends, and for how long it was down
     *
     * @param \MisterPhilip\MaintenanceMode\Events\MaintenanceModeDisabled $maintenanceMode
     */
    public function handle(MaintenanceModeDisabled $maintenanceMode)
    {
        $startingTime = $maintenanceMode->time;
        Log::notice("Maintenance Mode Disabled, total downtime was " . Carbon::now()->diffForHumans($startingTime, true, true, 6));
    }
}