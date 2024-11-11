<?php

namespace App\Classes\Feeds;

use App\Classes\Repositories\AlertRepositoryInterface;
use App\Classes\Rss\Writer\Channel;
use App\Classes\Rss\Writer\Feed;
use App\Classes\Rss\Writer\Item;
use App\Classes\Rss\RssFeedInterface;
use App\Classes\Transformers\AlertTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use DateTimeImmutable;
use DateTime;
use DateInterval;

class GdpcAlertFeed implements RssFeedInterface, JsonFeedInterface
{
	/**
	 * @var Feed
	 */
	protected $rss;

	/**
	 * @var AlertRepositoryInterface
	 */
	protected $alertRepo;

	/**
	 * @var Manager
	 */
	protected $responseManager;

	protected $countryCode = null;

	protected $baseUrl = 'http://localhost';

	public $title = 'GDPC Combined Alert Feed';

	public $description = 'Combined alerts from the Red Cross GDPC';

	public $language = 'en-US';

	public $link = 'http://preparecenter.org';

	public $copyright = 'public domain';

	/**
	 * @var array
	 */
	protected $filterEventTypes = [];

	/**
	 * @var string
	 */
	protected $filterSeverity = null;

	/**
	 * @var bool
	 */
	protected $activeOnly = false;

	/**
	 * @var DateTime
	 */
	protected $startTimeFilter;

	/**
	 * @var DateTime
	 */
	protected $endTimeFilter;

	/**
	 * GdpcAlertFeed constructor.
	 *
	 * @param Feed $rss
	 * @param AlertRepositoryInterface $alertRepo
	 * @param Manager $responseManager
	 */
	public function __construct(Feed $rss, AlertRepositoryInterface $alertRepo, Manager $responseManager)
	{
		$this->alertRepo = $alertRepo;
		$this->rss = $rss;
		$this->responseManager = $responseManager;

		// set default date filters
		$now = new DateTimeImmutable('now');
		$this->endTimeFilter = $now->setTime(23, 59, 59);
		$this->startTimeFilter = $now->sub(new DateInterval('P30D'));
	}

	/**
	 * @param string $url
	 * @return $this
	 */
	public function setBaseUrl($url = 'http://localhost')
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException("{$url} is not a valid url");
		}

		$this->baseUrl = $url;

		return $this;
	}

	/**
	 * @param string $countryCode
	 * @return $this
	 */
	public function setCountry($countryCode)
	{
		$this->countryCode = $countryCode;

		return $this;
	}

	/**
	 * @param null $types
	 * @return $this
	 */
	public function setEventTypeFilter($types = null)
	{
		if (is_array($types)) {
			$this->filterEventTypes = $types;
		}

		if (is_string($types)) {
			$this->filterEventTypes = explode(',', $types);
		}

		return $this;
	}

	/**
	 * @param string $severity
	 * @return $this
	 */
	public function setSeverityFilter(string $severity = null)
	{
		if (!is_null($severity)) {
			$this->filterSeverity = $severity;
		}

		return $this;
	}

	/**
	 * @param bool $activeOnly
	 * @return $this
	 */
	public function setActiveOnlyFilter(bool $activeOnly)
	{
		$this->activeOnly = $activeOnly;

		return $this;
	}

	/**
	 * @param mixed $dateTime
	 * @return $this
	 */
	public function setEndTimeFilter($dateTime = null)
	{
		if (is_null($dateTime)) {
			$this->endTimeFilter = (new DateTime('now'))->setTime(23, 59, 59);

			return $this;
		}

		if ($dateTime instanceof DateTime) {
			$this->endTimeFilter = $dateTime;
		} else {
			$this->endTimeFilter = new DateTime($dateTime);
		}

		return $this;
	}

	/**
	 * @param mixed $dateTime
	 * @return $this
	 */
	public function setStartTimeFilter($dateTime = null)
	{
		if (is_null($dateTime)) {
			$this->startTimeFilter = (new DateTime('now'))->sub(new DateInterval('P30D'));

			return $this;
		}

		if ($dateTime instanceof DateTime) {
			$this->startTimeFilter = $dateTime;
		} else {
			$this->startTimeFilter = new DateTime($dateTime);
		}

		return $this;
	}

	/**
	 * @return Collection
	 */
	protected function loadAlerts()
	{
		return $this->alertRepo->findAlerts(
			$this->countryCode,
			$this->filterEventTypes,
			$this->filterSeverity,
			$this->activeOnly,
			$this->endTimeFilter,
			$this->startTimeFilter
		);
	}

	/**
	 * @return string
	 */
	protected function getFeedLink()
	{
		if ($this->countryCode) {
			return $this->baseUrl . '/org/' . strtolower($this->countryCode) . '/alerts/rss';
		} else {
			return $this->baseUrl . '/alerts/rss';
		}
	}

	/**
	 * Builds feed Rss
	 *
	 * @return string
	 */
	public function buildRss()
	{
		$alerts = $this->loadAlerts();
		$buildDate = new DateTime('now');

		$channel = new Channel();
		$channel->title = $this->title;
		$channel->description = $this->description;
		$channel->language = $this->language;
		$channel->link = $this->link;
		$channel->pubDate = $buildDate;
		$channel->copyright = $this->copyright;
		$channel->lastBuildDate = $buildDate;
		$channel->atomLink = $this->getFeedLink();

		foreach ($alerts as $alert) {

			$item = new Item();
			$item->title = $alert->event;
			$item->link = $alert->getPublicUrl();
			$item->isPermalink = true;
			$item->description = $alert->description;
			$item->pubdate = $alert->sent_date;
			$item->guid = $alert->getPublicUrl();

			$channel->addItem($item);
		}

		$this->rss->addChannel($channel);

		return $this->rss->render();
	}

	/**
	 * Builds json feed data
	 *
	 * @return string
	 */
	public function getResponseData()
	{
		$resource = new Collection($this->loadAlerts(), new AlertTransformer);
		$rootScope = $this->responseManager->createData($resource);

		return $rootScope->toArray();
	}
}
