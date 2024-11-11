<?php

namespace App\Console\Commands;

use App\Jobs\GenerateCapAlertJob;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Class BuildCapAlert
 *
 * Console command allowing CAP messages to be built from any stored alert data
 *
 * @package RedCrossApi\Console\Commands
 */
class BuildCapAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rc:build-cap-alert
							{id : primary key id of alert to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Builds a CAP XML file for the specified alert.';

    /**
     * App Dispatcher instance (Lumen doesn't support DispatchesJob trait)
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');

        // Dispatch job in the current process
        $this->dispatcher->dispatchNow(new GenerateCapAlertJob($id));

        $this->info('Done');
    }
}
