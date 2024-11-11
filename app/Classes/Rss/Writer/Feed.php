<?php

namespace App\Classes\Rss\Writer;

use \DateTime;
use \DOMDocument;

class Feed
{
	/**
	 * @var array
	 */
	protected $channels = [];

	/**
	 * @var string
	 */
	protected $link;

	public function addChannel(Channel $channel)
	{
		$this->channels[] = $channel;
	}

	/**
	 * Renders feed to RSS 2.0 XML
	 *
	 * @return string
	 */
	public function render()
	{
		$document = new DOMDocument('1.0', 'UTF-8');
		$document->preserveWhiteSpace = false;
		$document->formatOutput = true;

		$rss = $document->createElement('rss');
		$rssNode = $document->appendChild($rss); //add RSS element to XML node
		$rssNode->setAttribute('version', '2.0');
		$rssNode->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");

		foreach ($this->channels as $channelData) {

			$channel = $document->createElement('channel');
			$channelNode = $rssNode->appendChild($channel);

			// Escape content
			$title = $channelNode->appendChild($document->createElement('title'));
			$title->appendChild($document->createTextNode($channelData->title));

			$desc = $channelNode->appendChild($document->createElement('description'));
			$desc->appendChild($document->createTextNode($channelData->description));

			$channelNode->appendChild($document->createElement('link', $channelData->link));
			$channelNode->appendChild($document->createElement('language', $channelData->language));
			if ($channelData->copyright) {
				$copyright = $channelNode->appendChild($document->createElement('copyright'));
				$copyright->appendChild($document->createTextNode($channelData->copyright));
			}
			$channelNode->appendChild($document->createElement('pubDate', $channelData->pubDate->format(DateTime::RSS)));
			$channelNode->appendChild($document->createElement('lastBuildDate', $channelData->lastBuildDate->format(DateTime::RSS)));

			if ($channelData->atomLink) {
				$atomLink = $document->createElement('atom:link');
				$atomLink->setAttribute('href', $channelData->atomLink); //url of the feed
				$atomLink->setAttribute('rel', 'self');
				$atomLink->setAttribute('type', 'application/rss+xml');
				$channelNode->appendChild($atomLink);
			}

			foreach($channelData->items as $itemData) {

				$itemNode = $channelNode->appendChild($document->createElement('item'));
				$title = $itemNode->appendChild($document->createElement('title'));
				// Escape content
				$title->appendChild($document->createTextNode($itemData->title));

				$itemNode->appendChild($document->createElement('link', $itemData->link));

				if ($itemData->author) {
					$itemNode->appendChild($document->createElement('author', $itemData->author));
				}

				$guidLink = $document->createElement('guid', $itemData->guid);

				$guidLink->setAttribute('isPermaLink', ($itemData->isPermalink) ? 'true' : 'false');

				$itemNode->appendChild($guidLink);

				$descriptionNode = $itemNode->appendChild($document->createElement('description'));
				// Fill description node with CDATA content
				$descriptionContents = $document->createCDATASection(htmlentities($itemData->description));
				$descriptionNode->appendChild($descriptionContents);

				$pubDate = $document->createElement('pubDate', $itemData->pubdate->format(DateTime::RSS));
				$itemNode->appendChild($pubDate);
			}
		}

		return $document->saveXML();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string)$this->render();
	}
}
