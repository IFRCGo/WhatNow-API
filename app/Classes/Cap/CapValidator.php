<?php

namespace App\Classes\Cap;

class CapValidator
{
	const CAP_11 = 'CAP_11';
	const CAP_12 = 'CAP_12';

	protected $format = null;

	protected $errors = [];

	public function __construct($format = self::CAP_12)
	{
		// Set the format/spec to validate against
		$this->format = $format;
	}

	public function validate($xml)
	{
		libxml_use_internal_errors(true);

		$doc = new \DOMDocument();
		$doc->loadXML($xml);

		$path = $this->format === self::CAP_12 ? 'cap_12_schema.xsd' : 'cap_11_schema.xsd';

		if (!$doc->schemaValidate(dirname(__FILE__) . '/' . $path)) {

			$this->errors = libxml_get_errors();
			libxml_clear_errors();

			return false;
		}

		return true;
	}

	public function getErrors()
	{
		return $this->errors;
	}
}
