<?php

namespace App\Classes\Rss\Writer;

class Item
{
	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $link;

	/**
	 * @var string
	 */
	public $description;
	/**
	 * @var string
	 */
	public $guid;

	/**
	 * @var bool
	 */
	public $isPermalink;

	/**
	 * @var \DateTime
	 */
	public $pubdate;

	/**
	 * @var string
	 */
	public $author;
}
