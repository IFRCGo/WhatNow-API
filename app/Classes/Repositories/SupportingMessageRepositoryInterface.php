<?php

namespace App\Classes\Repositories;

interface SupportingMessageRepositoryInterface extends RepositoryInterface
{
   
    /**
     * @param $keyMessageId
     * @return mixed
     */
    public function findItemsByKeyMessageId($keyMessageId);
}