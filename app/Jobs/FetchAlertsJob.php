<?php

namespace App\Jobs;

use App\Classes\Services\StormApiService;
use App\Classes\Repositories\AlertRepositoryInterface;
use Illuminate\Support\Facades\Log;

class FetchAlertsJob extends Job
{
	/**
	 * @var StormApiService
	 */
	protected $stormApi;

	/**
	 * @var AlertRepositoryInterface
	 */
	protected $alertRepository;

	/**
	 * Create a new job instance
	 */
    public function __construct()
    {

    }

    /**
     * Execute the job.
	 *
	 * @param StormApiService $service
	 * @param AlertRepositoryInterface $alertRepo
     * @return void
     */
    public function handle(StormApiService $service, AlertRepositoryInterface $alertRepo)
    {
		$this->stormApi = $service;
		$this->alertRepository = $alertRepo;

		try {
			$alerts = $this->stormApi->fetchAlertsFeed();
		} catch (\Exception $e) {
			Log::error('Fetch alerts job failed', $e->getMessage());
		}

		foreach ($alerts as $data) {

			// @todo clean this up / move to importer class to handle edge cases & value casting etc.
			$alert = $this->alertRepository->newInstance([
				'country_code' => $data['country'],
				'language_code' => $data['language'],
				'event' => $data['event'],
				'headline' => $data['headline'],
				'description' => $data['description'],
				'area_polygon' => $data['area_polygon'],
				'area_description' => $data['area_description'],
				'type' => $data['type'],
				'status' => $data['status'],
				'scope' =>  $data['scope'],
				'category' => $data['category'],
				'urgency' => $data['urgency'],
				'severity' => $data['severity'],
				'certainty' => $data['certainty'],
				'sent_date' => $data['timestamps']['created'],
				'start_date' => $data['timestamps']['created'],
				'effective_date' => $data['timestamps']['effective'],
				'expiry_date' => $data['timestamps']['expiry']
			]);

			if (!$alert->validate()) {
				Log::error('Invalid alert data', $alert->errors());
			}

			$alert->save();
		}
    }
}
