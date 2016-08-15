<?php namespace MisterPhilip\MaintenanceMode\Console\Commands;

use File;
use Carbon\Carbon;
use Illuminate\Foundation\Console\DownCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class StartMaintenanceCommand
 *
 * @package MisterPhilip\MaintenanceMode
 */
class StartMaintenanceCommand extends DownCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'down {message?} {--view=}';

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
        $view = $this->option('view');

        // Add the file with our information
        File::put($path, $timestamp . '|message:' . $message . '|view:' . $view);

        $output = 'Application is now in maintenance mode';

        if($message) {
            $output .= ' with a message of  "' . $message . '"';
        }

        if($view && $this->laravel->view->exists($view)) {
            $output .= ' using view "' . $view . '"';
        }

        // Inform the sysadmin/developer of the changes and any errors
        $this->info($output);
        if($view && !$this->laravel->view->exists($view)) {
            $this->error('View "' . $view . '" doesn\'t exist. Falling back to configuration file');
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
            ['message', InputArgument::OPTIONAL, 'A message to display, optional'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['view', InputOption::VALUE_REQUIRED, 'The view to use instead of the one specified in the configuration, optional'],
        ];
    }
}