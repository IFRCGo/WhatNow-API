<?php

namespace App\Classes\Repositories;

use App\Models\WhatNowEntity;
use App\Models\WhatNowEntityTranslation;

interface WhatNowTranslationRepositoryInterface extends RepositoryInterface
{
	/**
	 * @param WhatNowEntity $entity
	 * @param array $translations
	 */
	public function addTranslations(WhatNowEntity $entity, array $translations);

	/**
	 * @param $id
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getLatestTranslations($id);

	/**
	 * @param $id
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getLatestPublishedTranslations($id, $lang = null);

	/**
	 * @param array $ids
	 * @returns void
	 */
	public function publishTranslationsById(array $ids);

	/**
	 * @param int $id
	 * @return WhatNowEntityTranslation
	 */
	public function getTranslationById($id);
}
