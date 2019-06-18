<?php

namespace SampleApp\Listeners;

use Log;
use MisterPhilip\MaintenanceMode\Events\MaintenanceModeEnabled;

class LogMaintenanceStarted
{
    /**
     * Log when maintenance mode starts and the message shown (if applicable)
     *
     * @param \MisterPhilip\MaintenanceMode\Events\MaintenanceModeEnabled $maintenanceMode
     */
    public function handle(MaintenanceModeEnabled $maintenanceMode)
    {
        $logMessage = "Maintenance Mode Enabled";
        if (!is_null($maintenanceMode->message) && $maintenanceMode->message !== "") {
            $logMessage .= " with a custom message: \"" . $maintenanceMode->message . "\"";
        }
        Log::alert($logMessage);
    }
}
