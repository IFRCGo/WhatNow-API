<?php

namespace App\Classes\Cap;

class Cap12Writer extends AbstractWriter
{
	static public $xmlns = 'urn:oasis:names:tc:emergency:cap:1.2';

	protected function buildAlert()
	{
		$alert = $this->document->createElement('alert');
		$alertNode = $this->document->appendChild($alert);
		$alertNode->setAttribute('xmlns', self::$xmlns);

		$alertNode->appendChild($this->document->createElement('identifier', $this->alert->getCapIdentifier()));
		$alertNode->appendChild($this->document->createElement('sender', $this->alert->getSender()));
		$alertNode->appendChild($this->document->createElement('sent', $this->alert->sent_date->format('c')));
		$alertNode->appendChild($this->document->createElement('status', ucfirst($this->alert->status)));
		$alertNode->appendChild($this->document->createElement('msgType', ucfirst($this->alert->type)));
		$alertNode->appendChild($this->document->createElement('scope', ucfirst($this->alert->scope)));
	}

	protected function buildInfo()
	{
		$alertNode = $this->document->getElementsByTagName('alert')->item(0);

		$infoNode = $alertNode->appendChild($this->document->createElement('info'));
		$infoNode->appendChild($this->document->createElement('language', $this->alert->language_code));
		$infoNode->appendChild($this->document->createElement('category', ucfirst($this->alert->category)));
		$infoNode->appendChild($this->document->createElement('event', $this->alert->event));

		$infoNode->appendChild($this->document->createElement('urgency', ucfirst($this->alert->urgency)));
		$infoNode->appendChild($this->document->createElement('severity', ucfirst($this->alert->severity)));
		$infoNode->appendChild($this->document->createElement('certainty', ucfirst($this->alert->certainty)));

		if ($this->alert->effective_date) {
			$infoNode->appendChild($this->document->createElement('effective',
				$this->alert->effective_date->format('c')));
		}

		if ($this->alert->onset_date) {
			$infoNode->appendChild($this->document->createElement('onset', $this->alert->onset_date->format('c')));
		}

		if ($this->alert->expiry_date) {
			$infoNode->appendChild($this->document->createElement('expires', $this->alert->expiry_date->format('c')));
		}

		// Escape free text elements
		$sender = $infoNode->appendChild($this->document->createElement('senderName'));
		$sender->appendChild($this->document->createTextNode($this->alert->getSenderName()));

		$headline = $infoNode->appendChild($this->document->createElement('headline'));
		$headline->appendChild($this->document->createTextNode($this->alert->headline));

		$desc = $infoNode->appendChild($this->document->createElement('description'));
		$desc->appendChild($this->document->createTextNode($this->alert->description));

		$infoNode->appendChild($this->document->createElement('web', $this->alert->getPublicUrl()));

		// Alert area
		$areaNode = $infoNode->appendChild($this->document->createElement('area'));

		$areaDesc = $areaNode->appendChild($this->document->createElement('areaDesc'));
		$areaDesc->appendChild($this->document->createTextNode($this->alert->area_description));

		if ($this->alert->area_polygon) {
			$polygon = $areaNode->appendChild($this->document->createElement('polygon'));
			$polygon->appendChild($this->document->createTextNode($this->alert->getAreaPolygonString()));
		}
	}

	public function buildXml()
	{
		if (!$this->alert) {
			throw new \Exception('Alert model property is not set');
		}

		$this->buildAlert();
		$this->buildInfo();

		return $this->document->saveXML();
	}
}
