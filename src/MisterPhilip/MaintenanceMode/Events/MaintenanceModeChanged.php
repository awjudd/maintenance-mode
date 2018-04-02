<?php

namespace MisterPhilip\MaintenanceMode\Events;

use Carbon\Carbon;

abstract class MaintenanceModeChanged
{

    /**
     * The timestamp the application went down
     *
     * @var Carbon|null
     */
    public $time;

    /**
     * The custom message shown to the end users
     *
     * @var string|null
     */
    public $message;

    /**
     * The view name to use
     *
     * @var string|null
     */
    public $view;

    /**
     * The number of seconds to send in the Retry-After header
     *
     * @var int|null
     */
    public $retry;

    /**
     * Build a new event when Maintenance Mode is disabled
     *
     * @param int|null $time
     * @param string|null $message
     * @param string|null $view
     * @param int|null $retry
     */
    public function __construct($time = null, $message = null, $view = null, $retry = null)
    {
        if(!is_null($time))
        {
            $this->time = Carbon::createFromTimestamp($time);
        }

        if(!is_null($message))
        {
            $this->message = $message;
        }

        if(!is_null($view))
        {
            $this->view = $view;
        }

        if(!is_null($retry))
        {
            $this->retry = $retry;
        }
    }
}
