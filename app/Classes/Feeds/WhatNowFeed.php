<?php

namespace App\Classes\Feeds;

use Illuminate\Database\Eloquent\Collection;
use League\Fractal\Manager;
use App\Classes\Repositories\WhatNowRepositoryInterface;
use App\Classes\Repositories\WhatNowTranslationRepositoryInterface;
use App\Classes\Serializers\CustomDataSerializer;
use App\Classes\Transformers\WhatNowEntityTransformer;
use App\Models\Organisation;
use App\Models\Region;
use App\Models\WhatNowEntity;

class WhatNowFeed implements JsonFeedInterface
{
	/**
	 * @var string
	 */
	protected $language;

	/**
	 * @var Region
	 */
	protected $region;

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
	 * @param null $region
	 * @return $this
	 */
	public function setRegion(Region $region = null)
	{
		$this->subnational = $region;
		return $this;
	}

	/**
	 * @param string $lang
	 * @return $this
	 */
	public function setLanguage($lang = null)
	{
		$this->language = $lang ? substr($lang, 0, 2) : null;
		$this->transformer->setLang($this->language);
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
			$this->filterEventTypes,
			$this->subnational->id ?? null
		);

		if ($data instanceof Collection) {

			// Exclude any entity that does not have a published revision yet
			$this->collection = $data->reject(function(WhatNowEntity $entity){
				// this does lots of queries, could be optimised
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
