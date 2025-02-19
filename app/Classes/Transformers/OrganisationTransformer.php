<?php

namespace App\Classes\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Organisation;

class OrganisationTransformer extends TransformerAbstract
{
	/**
	 * Whether to include unpublished details or not
	 *
	 * @var bool
	 */
	private $unpublished = false;

	/**
	 * @param array $configuration
	 */
	function __construct($configuration = [])
	{
		if(isset($configuration['unpublished']) && is_bool($configuration['unpublished'])) {
			$this->unpublished = $configuration['unpublished'];
		}
	}

	/**
	 * Turn this item object into a generic array
	 *
	 * @param Organisation $model
	 * @return array
	 */
	public function transform(Organisation $model)
	{
		$response = [
			'countryCode' => $model->country_code,
			'name' => $model->org_name,
			'url' => $model->attribution_url,
			'imageUrl' => $model->attribution_file_name ? $model->getAttributionImageUrl() : null,
			'translations' => null,
		];

		if ($model->details->count()) {
			$response['translations'] = [];

			foreach ($model->details as $detail) {

				$model->details->each(function ($detail) {
					$detail->load('contributors');
				});

				if($this->unpublished || $detail->published) {
					$response['translations'][$detail->language_code] = [
						'languageCode' => $detail->language_code,
						'name' => $detail->org_name,
						'attributionMessage' => $detail->attribution_message,
					];

					$response['translations'][$detail->language_code]['contributors'] = [];
					if ($detail->contributors->count()) {
						
						$response['translations'][$detail->language_code]['contributors'] = $detail->contributors->map(function ($contributor) {
							return [
								'id' => $contributor->id,
								'name' => $contributor->name,
								'logo' => $contributor->logo,
							];
						});
					}

					$response['translations'][$detail->language_code]['published'] = (bool) $detail->published;
				}
			}
		}

		return $response;
	}
}
