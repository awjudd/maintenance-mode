<?php namespace MisterPhilip\MaintenanceMode\Events;

class MaintenanceModeEnabled
{

    /**
     * The maintenance mode information
     *
     * @var array
     */
    public $info;

    /**
     * @param        $payload
     */
    public function __construct($payload)
    {
        $this->info = $payload;
    }
}
