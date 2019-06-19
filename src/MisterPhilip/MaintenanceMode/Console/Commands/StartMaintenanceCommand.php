<?php

namespace MisterPhilip\MaintenanceMode\Console\Commands;

use File;
use Event;
use Illuminate\Foundation\Console\DownCommand;
use MisterPhilip\MaintenanceMode\Events\MaintenanceModeEnabled;

/**
 * Class StartMaintenanceCommand
 *
 * @package MisterPhilip\MaintenanceMode
 */
class StartMaintenanceCommand extends DownCommand
{
    /**
     * Flag to abort the command (e.g. bad view selected)
     *
     * @var bool
     */
    protected $abort = false;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'down {--message= : The message for the maintenance mode. }
            {--retry= : The number of seconds after which the request may be retried.}
	    {--view= : The view to use for this instance of maintenance mode.}
	    {--allow=* : IP or networks allowed to access the application while in maintenance mode.}';

    /**
     * Execute the maintenance mode command
     *
     * @return bool
     */
    public function handle()
    {
        if (!$this->verifyViewExists($this->option('view'))) {
            $message  = "Aborting due to missing view. Your application will remain ";
            $message .= (file_exists($this->laravel->storagePath().'/framework/down')) ? "down from a previous down command." : "up.";
            $this->info($message);
            return false;
        }

        // Call the original method, writing to the down file & console output
        parent::handle();

        // Fire an event
        $payload = $this->getDownFilePayload();
        Event::dispatch(new MaintenanceModeEnabled($payload['time'], $payload['message'], $payload['view'], $payload['retry'], $payload['allowed']));
        return true;
    }

    /**
     * Get the payload to be placed in the "down" file.
     *
     * @return array
     */
    protected function getDownFilePayload()
    {
        // Get the Laravel file data & add ours (selected view)
        $data = parent::getDownFilePayload();
        $data['view'] = $this->option('view');

        if (!isset($data['allowed'])) {
            $data['allowed'] =  $this->option('allow');
        }
        return $data;
    }

    /**
     * Verify the view exists, and if not then prompt if they want to continue with the default
     *
     * @param $view
     * @return bool
     */
    protected function verifyViewExists($view)
    {
        // Verify the user passed us a correct view
        if ($view && !$this->laravel->view->exists($view)) {
            $this->laravel->config->get("maintenancemode.view");
            $this->error("The view \"{$view}\" does not exist. If you continue, the view from the configuration file \"{$this->laravel->config->get("maintenancemode.view")}\" will be used until \"{$view}\" is found.");
            return $this->confirm('Do you wish to continue? [y|N]');
        }
        return true;
    }
}
