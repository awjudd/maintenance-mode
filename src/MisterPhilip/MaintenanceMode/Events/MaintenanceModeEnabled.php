<?php namespace MisterPhilip\MaintenanceMode\Events;

class MaintenanceModeEnabled
{

    /**
     * Timestamp of when the application was brought down
     *
     * @var string
     */
    public $timestamp;

    /**
     * The message the application was brought down with
     *
     * @var string
     */
    public $message;

    /**
     * @param        $timestamp
     * @param string $message
     */
    public function __construct($timestamp, $message = '')
    {
        $this->timestamp    = $timestamp;
        $this->message      = $message;
    }
}
