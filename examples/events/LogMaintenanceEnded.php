<?php

namespace SampleApp\Listeners;

use Log;
use Carbon\Carbon;
use MisterPhilip\MaintenanceMode\Events\MaintenanceModeDisabled;

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
