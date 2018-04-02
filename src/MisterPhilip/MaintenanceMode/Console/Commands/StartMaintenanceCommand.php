<?php namespace MisterPhilip\MaintenanceMode\Console\Commands;

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
            {--view= : The view to use for this instance of maintenance mode.}';

    /**
     * Execute the maintenance mode command
     *
     * @return void
     */
    public function handle()
    {
        $payload = $this->getDownFilePayload();
        if($this->abort)
        {
            return false;
        }

        file_put_contents(
            $this->laravel->storagePath().'/framework/down',
            json_encode($payload, JSON_PRETTY_PRINT)
        );
        $this->comment('Application is now in maintenance mode.');

        // Fire an event
        Event::fire(new MaintenanceModeEnabled($payload));
    }

    /**
     * Get the payload to be placed in the "down" file.
     *
     * @return array
     */
    protected function getDownFilePayload()
    {
        return [
            'time' => time(),
            'message' => $this->option('message'),
            'retry' => $this->getRetryTime(),
            'view'  => $this->getSelectedView(),
        ];
    }

    /**
     * Get the selected view, if one exists
     *
     * @return string
     */
    protected function getSelectedView()
    {
        $view = $this->option('view');

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