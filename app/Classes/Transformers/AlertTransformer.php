<?php

namespace App\Classes\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Alert;

class AlertTransformer extends TransformerAbstract
{
	/**
	 * Turn this item object into a generic array
	 *
	 * @param Alert $model
	 * @return array
	 */
	public function transform(Alert $model)
	{
		return [
			'identifier' => $model->getCapIdentifier(),
			'sender' => $model->getSender(),
			'sent' => $model->sent_date ? $model->sent_date->format('c') : null,
			'status' => ucfirst((string) $model->status),
			'msg_type' => ucfirst((string) $model->type),
			'scope' => ucfirst((string) $model->scope),
			'info' => [
				'language' => $model->language_code,
				'category' => ucfirst((string) $model->category),
				'event' => $model->event,
				'urgency' => ucfirst((string) $model->urgency),
				'severity' => ucfirst((string) $model->severity),
				'certainty' => ucfirst((string) $model->urgency),
				'effective' => $model->effective_date ? $model->effective_date->format('c') : null,
				'onset' => $model->onset_date ? $model->onset_date->format('c') : null,
				'expires' => $model->expiry_date ? $model->expiry_date->format('c') : null,
				'sender_name' => $model->getSenderName(),
				'headline' => $model->headline,
				'description' => $model->description,
				'area' => [
					'area_desc' => $model->area_description,
					'polygon' => [
						'type' => 'Feature',
						'geometry' => $model->area_polygon
					]
				]
			],
			'cap_url' => $model->getPublicUrl(),
			'organisation' => [
				'country' => $model->organisation->country_code,
				'name' => $model->organisation->org_name,
				'oid_code' => $model->organisation->oid_code
			]
		];
	}
}
