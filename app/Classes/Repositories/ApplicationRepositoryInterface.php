<?php

namespace App\Classes\Repositories;

use App\Models\Application;

interface ApplicationRepositoryInterface extends RepositoryInterface
{
    /**
     * @param $tenantId
     * @param $userId
     * @return Application[]
     */
    public function findForUserId($tenantId, $userId);

    public function allDesc($columns);
}
