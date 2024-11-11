<?php

namespace App\Classes\Repositories;

interface AlertRepositoryInterface extends RepositoryInterface
{
	public function getByIdentifier($identifier);
}
