<?php

namespace App\Classes\Repositories;

use App\Models\Organisation;

interface OrganisationRepositoryInterface extends RepositoryInterface
{
	/**
	 * @param bool $published
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function allByTranslationPublished($published);

	/**
	 * @param $code
	 * @return mixed
	 */
	public function findByCountryCode($code);

	/**
	 * @param Organisation $org
	 * @param array $input
	 * @return mixed
	 */
	public function updateDetailsWithInput(Organisation $org, array $input);
}
