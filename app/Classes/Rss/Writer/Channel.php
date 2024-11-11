<?php

namespace App\Classes\Rss\Writer;

class Channel
{
	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $language;

	/**
	 * @var string
	 */
	public $copyright;

	/**
	 * @var string
	 */
	public $link;

	/**
	 * @var string
	 */
	public $atomLink;

	/**
	 * @var \DateTime
	 */
	public $lastBuildDate;

	/**
	 * @var \DateTime
	 */
	public $pubDate;

	/**
	 * @var array
	 */
	public $items = [];

	/**
	 * @param Item $item
	 */
	public function addItem(Item $item)
	{
		$this->items[] = $item;
	}
}
