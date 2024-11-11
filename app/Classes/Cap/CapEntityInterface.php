<?php

namespace App\Classes\Cap;

interface CapEntityInterface
{
	/**
	 * @return string
	 */
	public function getAreaPolygonString();

	/**
	 * @return string
	 */
	public function getXmlPath();

	/**
	 * @return mixed
	 */
	public function getCapIdentifier();

	/**
	 * @return mixed
	 */
	public function getSenderName();

	/**
	 * @param $extension
	 * @return mixed
	 */
	public function getPublicUrl($extension);
}
