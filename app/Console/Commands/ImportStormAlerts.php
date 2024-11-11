<?php

namespace App\Console\Commands;

use App\Jobs\FetchAlertsJob;
use Illuminate\Console\Command;

class ImportStormAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rc:import-storm-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches and imports new alerts from Storm APIs.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting import');

        dispatch(new FetchAlertsJob);

        $this->info('Done');
    }
}
