<?php

namespace SampleApp\Listeners;

use Log;
use MisterPhilip\MaintenanceMode\Events\MaintenanceModeEnabled;

class LogMaintenanceStarted
{
    public function handle(MaintenanceModeEnabled $maintenanceMode)
    {
        $logMessage = "Maintenance Mode Enabled";
        if($maintenanceMode->message)
        {
            $logMessage .= " with a custom message: \"" . $maintenanceMode->message . "\"";
        }
        Log::alert($logMessage);
    }
}