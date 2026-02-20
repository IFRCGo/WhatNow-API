<?php

namespace App\Legacy\Classes\Feeds;

use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Manager;
use App\Legacy\Classes\Repositories\WhatNowRepositoryInterface;
use App\Legacy\Classes\Repositories\WhatNowTranslationRepositoryInterface;
use App\Legacy\Classes\Serializers\CustomDataSerializer;
use App\Legacy\Classes\Transformers\WhatNowEntityTransformer;
use App\Legacy\Models\Organisation;
use App\Legacy\Models\WhatNowEntity;

class WhatNowFeed implements JsonFeedInterface
{
	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var Organisation
	 */
	protected $organisation;

	/**
	 * @var WhatNowRepositoryInterface
	 */
	protected $whatNowRepo;

	/**
	 * @var WhatNowTranslationRepositoryInterface
	 */
	protected $whatNowTransRepo;

	/**
	 * @var WhatNowEntityTransformer
	 */
	protected $transformer;

	/**
	 * @var array
	 */
	protected $filterEventTypes = [];

	/**
	 * @var Manager
	 */
	protected $responseManager;

	/**
	 * @var
	 */
	protected $collection = null;

	/**
	 * @param WhatNowRepositoryInterface            $whatNowRepo
	 * @param WhatNowTranslationRepositoryInterface $whatNowTransRepo
	 * @param Manager                               $responseManager
	 * @param WhatNowEntityTransformer              $transformer
	 */
	public function __construct(
		WhatNowRepositoryInterface $whatNowRepo,
		WhatNowTranslationRepositoryInterface $whatNowTransRepo,
		Manager $responseManager,
		WhatNowEntityTransformer $transformer
	) {
		$this->whatNowRepo = $whatNowRepo;
		$this->whatNowTransRepo = $whatNowTransRepo;
		$this->responseManager = $responseManager;
		$this->transformer = $transformer;

		$this->responseManager->setSerializer(new CustomDataSerializer());
	}

	/**
	 * @param Organisation $org
	 * @return $this
	 */
	public function setOrganisation(Organisation $org)
	{
		$this->organisation = $org;

		return $this;
	}

	/**
	 * @param string $lang
	 * @return $this
	 */
	public function setLanguage($lang = 'en_US')
	{
		// @todo validate locale
		$this->language = $lang;

		return $this;
	}

	/**
	 * @param null $types
	 * @return $this
	 */
	public function  setEventTypeFilter($types = null)
	{
		if (is_array($types)) {
			$this->filterEventTypes = $types;
		}

		if (is_string($types)) {
			$this->filterEventTypes = explode(',', $types);
		}

		return $this;
	}

	public function loadData()
	{
		$data = $this->whatNowRepo->findItemsForOrgId(
			$this->organisation->id,
			$this->language,
			$this->filterEventTypes
		);

		if ($data instanceof Collection) {


			$this->collection = $data->reject(function(WhatNowEntity $entity){

				return ($this->whatNowTransRepo->getLatestPublishedTranslations($entity->id)->count() === 0);
			});
		}
	}

	public function getCollection()
	{
		return $this->collection;
	}

	public function getResponseData()
	{
		$resource = new \League\Fractal\Resource\Collection($this->collection, $this->transformer);
		$rootScope = $this->responseManager->createData($resource);
		return $rootScope->toArray();
	}
}
