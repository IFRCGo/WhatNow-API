<?php

namespace App\Classes\Services;

class StormApiService
{
	/**
	 * @var string
	 */
	protected $base_url = 'https://gdpc.cubeapis.com/latest';

	/**
	 * @param $url
	 * @return string
	 */
	protected function get($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			throw new \InvalidArgumentException("{$url} is not a valid url");
		}

		$opts = [
			'http' => [
				'method' => 'GET'
			]
		];

		$context = stream_context_create($opts);

		return file_get_contents($url, false, $context);
	}

	/**
	 * @return bool|mixed
	 */
	public function fetchAlertsFeed()
	{
		$response = $this->get($this->base_url . '/alerts/feed');

		if ($response) {
			return json_decode($response);
		}

		return false;
	}
}
