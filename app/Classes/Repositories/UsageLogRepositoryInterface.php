<?php

namespace App\Classes\Repositories;

interface UsageLogRepositoryInterface extends RepositoryInterface
{
    public function getForApplication($applicationId, $fromDate = null, $toDate = null);

    public function getForEndpoint($endpoint, $fromDate = null, $toDate = null);
}
