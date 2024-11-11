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
			'sent' => $model->sent_date->format('c'),
			'status' => ucfirst($model->status),
			'msg_type' => ucfirst($model->type),
			'scope' => ucfirst($model->scope),
			'info' => [
				'language' => $model->language_code,
				'category' => ucfirst($model->category),
				'event' => $model->event,
				'urgency' => ucfirst($model->urgency),
				'severity' => ucfirst($model->severity),
				'certainty' => ucfirst($model->urgency),
				'effective' => $model->effective_date->format('c'),
				'onset' => $model->onset_date->format('c'),
				'expires' => $model->expiry_date->format('c'),
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
