<?php namespace MisterPhilip\MaintenanceMode\Console\Commands;

use File;
use Carbon\Carbon;
use Illuminate\Foundation\Console\DownCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class StartMaintenanceCommand
 *
 * @package MisterPhilip\MaintenanceMode
 */
class StartMaintenanceCommand extends DownCommand {

    /**
     * Execute the maintenance mode command
     *
     * @return void
     */
    public function fire()
    {
        $path = $this->laravel->storagePath().'/framework/down';

        $timestamp = Carbon::now()->timestamp;
        $message = $this->argument('message');

        // Add the file with our information
        File::put($path, $timestamp . '|' . $message);

        // Inform the sysadmin/developer of the changes
        if($message)
        {
            $this->comment('Application is now in maintenance mode with a message of "' . $message . '".' );
        }
        else
        {
            $this->comment('Application is now in maintenance mode.');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['message', InputArgument::OPTIONAL, 'An optional message to display'],
        ];
    }
}