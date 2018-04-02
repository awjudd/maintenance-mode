<?php namespace MisterPhilip\MaintenanceMode\Console\Commands;

use Event;
use Illuminate\Foundation\Console\UpCommand;
use MisterPhilip\MaintenanceMode\Events\MaintenanceModeDisabled;

/**
 * Class StartMaintenanceCommand
 *
 * @package MisterPhilip\MaintenanceMode
 */
class EndMaintenanceCommand extends UpCommand
{
    /**
     * Execute the maintenance mode command
     *
     * @return mixed
     */
    public function handle()
    {
        // Verify we're actually down!
        if(!file_exists($this->laravel->storagePath().'/framework/down'))
        {
            $this->info('Application is already live.');
            return false;
        }

        // Grab the data
        $data = @json_decode(file_get_contents($this->laravel->storagePath().'/framework/down'), true);
        if(!isset($data) || is_array($data))
        {
            $data = [];
        }
        $data = array_merge(["time" => null, "message" => null, "view" => null, "retry" => null], $data);

        // Call the original method, unlinking the down file & console output
        parent::handle();

        // Fire the event
        Event::fire(new MaintenanceModeDisabled($data['time'], $data['message'], $data['view'], $data['retry']));
    }
}