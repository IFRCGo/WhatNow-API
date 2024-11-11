<?php

namespace App\Classes\Repositories;

interface WhatNowRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $orgId
     * @param null $lang
     * @param array $eventTypes
     */
    public function findItemsForOrgId($orgId, $lang = null, array $eventTypes = []);

    /**
     * @param $orgId
     * @param null $lang
     * @param array $eventTypes
     * @param string $regionName
     */
    public function findItemsForRegionByOrgId($orgId, $lang = null, array $eventTypes = [], $regionName = null);

	/**
	 * @param array $array
	 * @return static
	 */
	public function createFromArray(array $array);
}
