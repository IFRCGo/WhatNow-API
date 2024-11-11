<?php

namespace App\Classes\Repositories;

interface UsageLogRepositoryInterface extends RepositoryInterface
{
    public function getForApplication(int $applicationId);

    public function getForEndpoint(string $endpoint);
}
