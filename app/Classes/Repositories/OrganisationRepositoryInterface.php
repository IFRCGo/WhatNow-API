<?php

namespace App\Classes\Repositories;

use App\Models\Organisation;

interface OrganisationRepositoryInterface extends RepositoryInterface
{
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
