<?php

namespace App\Classes\Repositories;

interface RegionRepositoryInterface
{
    /**
	 * @param $regionName
	 * @return mixed
	 */
	public function findBySlug($orgId, $slug);

}
