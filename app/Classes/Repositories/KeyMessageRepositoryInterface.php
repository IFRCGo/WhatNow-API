<?php

namespace App\Classes\Repositories;

interface KeyMessageRepositoryInterface extends RepositoryInterface
{
   
    /**
     * @param $stageId
     * @return mixed
     */
    public function findItemsByStageId($stageId);
}