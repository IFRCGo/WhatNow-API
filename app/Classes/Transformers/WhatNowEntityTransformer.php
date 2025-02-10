<?php

namespace App\Classes\Transformers;

use League\Fractal\TransformerAbstract;
use App\Classes\Repositories\WhatNowRepository;
use App\Classes\Repositories\WhatNowTranslationRepositoryInterface;
use App\Models\WhatNowEntity;
use App\Models\WhatNowEntityTranslation;

class WhatNowEntityTransformer extends TransformerAbstract
{
	/**
	 * @var bool
	 */
	private $unpublished = false;

	/**
	 * @var bool
	 */
	private $castDateToBoolean = true;

	/** @var WhatNowTranslationRepositoryInterface */
	private $wnTransRepo;

	/**
	 * @param WhatNowTranslationRepositoryInterface $repo
	 * @param array $configuration
	 */

	function __construct(WhatNowTranslationRepositoryInterface $repo, $configuration = [])
	{
		if(isset($configuration['unpublished']) && is_bool($configuration['unpublished'])) {
			$this->unpublished = $configuration['unpublished'];
		}

        if(isset($configuration['castDateToBoolean']) && is_bool($configuration['castDateToBoolean'])) {
            $this->castDateToBoolean = $configuration['castDateToBoolean'];
        }

		$this->wnTransRepo = $repo;
	}

	/**
	 * Turn this item object into a generic array
	 *
	 * @param WhatNowEntity $model
	 * @return array
	 */
	public function transform(WhatNowEntity $model)
	{
		$response = [
			'id' => (string) $model->id,
			'countryCode' => $model->organisation->country_code,
			'eventType' => $model->event_type,
            'regionName' => $model->region_name,
            'region' => $model->region,
			'attribution' => [
				'name' => $model->organisation->org_name,
				'countryCode' => $model->organisation->country_code,
				'url' => $model->organisation->attribution_url,
				'imageUrl' => $model->organisation->attribution_file_name ? $model->organisation->getAttributionImageUrl() : null,
				'translations' => null
			],
		];

		if ($model->organisation->details->count()) {
			$response['attribution']['translations'] = [];

			foreach ($model->organisation->details as $detail) {
				$response['attribution']['translations'][$detail->language_code] = [
					'languageCode' => $detail->language_code,
					'name' => $detail->org_name,
					'attributionMessage' => $detail->attribution_message,
				];

				$response['attribution']['translations'][$detail->language_code]['published'] = (bool) $detail->published;
			}
		}

		if ($this->unpublished) {
			$translations = $this->wnTransRepo->getLatestTranslations($model->id) ?? [];
		} else {
			$translations = $this->wnTransRepo->getLatestPublishedTranslations($model->id) ?? [];
		}

		$defaultStages = [];
		foreach(WhatNowRepository::EVENT_STAGES as $eventStages) {
			$defaultStages[$eventStages] = null;
		}

		if ($translations) {
			$response['translations'] = [];
			/** @var WhatNowEntityTranslation $trans */
			foreach ($translations as $trans) {

				$stages = $defaultStages;
				if($trans->stages){
					foreach ($trans->stages as $stage) {
						$stage->load('keyMessages');
						$keyMessages = $stage->keyMessages;

						foreach ($keyMessages as $keyMessage) {
							$keyMessage->load('supportingMessages');
						}

						$stages[$stage->stage] = $keyMessages->map(function($keyMessage) {
							return [
								'title' => $keyMessage->title,
								'content' => $keyMessage->supportingMessages->map(function($supportingMessage) {
									return $supportingMessage->content;
								})->toArray()
							];
						})->toArray();
					}
				}

				$response['translations'][$trans->language_code] = [
					'id' => (string) $trans->id,
					'lang' => $trans->language_code,
					'webUrl' => $trans->web_url,
					'title' => $trans->title,
					'description' => $trans->description,
					'published' => $this->prepareDateField($trans->published_at),
					'createdAt' => $trans->created_at->format('c'),
					'stages' => $stages
				];
			}
		}

		return $response;
	}

    protected function prepareDateField($date)
    {
        if($this->castDateToBoolean)
        {
            return ($date instanceof \DateTimeInterface) ? true : false;
		}

        return ($date instanceof \DateTimeInterface) ? $date->format('c') : null;
    }
}


