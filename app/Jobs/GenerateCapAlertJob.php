<?php

namespace App\Jobs;

use Illuminate\Contracts\Validation\ValidationException;
use App\Classes\Cap\Cap12Writer;
use App\Classes\Cap\CapValidator;
use App\Classes\Repositories\AlertRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\CapAlertCreatedEvent;
use Illuminate\Support\Facades\Event;


class GenerateCapAlertJob extends Job
{
	/**
	 * @var AlertRepositoryInterface
	 */
	protected $alertRepository;

	/**
	 * @var
	 */
	protected $alertId;

	/**
	 * Create a new job instance
	 *
	 * @param alertId
	 */
	public function __construct($alertId)
	{
		$this->alertId = $alertId;
	}

	/**
	 * @param AlertRepositoryInterface $alertRepo
	 * @param Cap12Writer $writer
	 * @param CapValidator $validator
	 * @param Filesystem $storage
	 * @throws \Exception
	 */
	public function handle(
		AlertRepositoryInterface $alertRepo,
		Cap12Writer $writer,
		CapValidator $validator
	) {
		$this->alertRepository = $alertRepo;

		try {
			$alert = $this->alertRepository->find($this->alertId);
		} catch (\Exception $e) {
			Log::error('Generate cap alert job failed', ['message' => $e->getMessage()]);
		}

		if ($alert) {

			// XSL url/path will be different in production.
			$xslPath = config('app.cdn_asset_path') . '/styles/gdpc_alert.xsl';
			$xslHost = app()->environment('production') ? config('app.cdn_host') : config('app.url');

			$writer->setAlertModel($alert)->setXsl(url($xslHost . $xslPath));
			$xml = $writer->buildXml();

			if (!$validator->validate($xml)) {

				Log::error('Cap generator validation failed', ['message' => $validator->getErrors()]);
				throw new \Exception('Cap generator validation failed');
			}

			$disk = app()->environment('local', 'testing') ? 'public' : 's3';

			// Create alert XML file
			$file = Storage::disk($disk)->put(config('app.cdn_alert_path') . '/' . $alert->getXmlPath(), $xml, 'public');

			// Fire created event to trigger listeners
			//Event::fire(new CapAlertCreatedEvent($alert));
		}
	}
}
