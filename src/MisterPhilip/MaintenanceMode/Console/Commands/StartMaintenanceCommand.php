<?php

namespace MisterPhilip\MaintenanceMode\Console\Commands;

use File, Event;
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
     * @return bool|void
     */
    public function handle()
    {
        if($this->abort)
        {
            return false;
        }

        // Call the original method, writing to the down file & console output
        parent::handle();

        // Fire an event
        $payload = $this->getDownFilePayload();
        Event::fire(new MaintenanceModeEnabled($payload['time'], $payload['message'], $payload['view'], $payload['retry'], $payload['allowed']));
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
        $data['view'] = $this->getSelectedView();

        if(!isset($data['allowed'])) {
            $data['allowed'] =  $this->option('allow');
        }
        return $data;
    }

    /**
     * Get the selected view, if one exists
     *
     * @return string
     */
    protected function getSelectedView()
    {
        $view = $this->option('view');

        // Verify the user passed us a correct view
        if($view && !$this->laravel->view->exists($view))
        {
            $this->error("The view \"{$view}\" does not exist.");
            if(!$this->confirm('Do you wish to continue? [y|N]'))
            {
                $this->abort = true;
            }
            else
            {
                $this->info('OK, falling back to the view defined in the config file.');
            }
        }

        return $view;
    }
}
