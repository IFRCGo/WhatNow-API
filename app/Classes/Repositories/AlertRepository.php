<?php

namespace App\Classes\Repositories;

use App\Models\Alert;
use DateTimeInterface;
use DateTime;

class AlertRepository implements AlertRepositoryInterface
{

	/**
	 * @var Alert
	 */
	protected $alertModel;

	/**
	 * @param Alert $alertModel
	 */
	public function __construct(Alert $alertModel)
	{
		$this->alertModel = $alertModel;
	}

	/**
	 * @param array $attributes
	 * @return static
	 */
	public function newInstance(array $attributes = [])
	{
		return $this->alertModel->newInstance($attributes);
	}

	/**
	 * @param array $columns
	 */
	public function all($columns = ['*'])
	{
		return $this->alertModel->all($columns);
	}

	/**
	 * @param array $attributes
	 * @return static
	 */
	public function create(array $attributes)
	{
		return $this->alertModel->create($attributes);
	}

	/**
	 * @param $id
	 * @param array $columns
	 * @return mixed
	 */
	public function find($id, $columns = ['*'])
	{
		return $this->alertModel->findOrFail($id, $columns);
	}

	/**
	 * @param $id
	 * @param array $input
	 * @return mixed
	 */
	public function updateWithIdAndInput($id, array $input)
	{
		return $this->alertModel->where('id', $id)->update($input);
	}

	/**
	 * @param $id
	 * @return int
	 */
	public function destroy($id)
	{
		return $this->alertModel->destroy($id);
	}

	/**
	 * @param null $country
	 * @param array $eventTypes
	 * @param string|null $severity
	 * @param bool $activeOnly
	 * @param DateTimeInterface|null $endDate
	 * @param DateTimeInterface|null $startDate
	 * @return mixed
	 */
	public function findAlerts(
		$country = null,
		array $eventTypes = [],
		string $severity = null,
		$activeOnly = false,
		DateTimeInterface $endDate = null,
		DateTimeInterface $startDate = null
	) {
		$query = $this->alertModel->query();

		if ($country) {
			$query->where('country_code', $country);
		}

		if (count($eventTypes)) {
			$query->whereIn('event', $eventTypes);
		}

		if ($severity) {
			$query->where('severity', $severity);
		}

		if ($activeOnly) {
			$query->where('expiry_date', '>', new DateTime('now'));
		}

		if ($startDate && $endDate) {
			$query->whereBetween('sent_date', [$startDate, $endDate]);
		}

		return $query->orderBy('sent_date', 'desc')->with('organisation')->get();
	}

	/**
	 * @param $identifier
	 * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
	 */
	public function getByIdentifier($identifier)
	{
		$idComponents = explode('.', $identifier);
		$id = end($idComponents);

		if (!is_numeric($id)) {
			throw new \InvalidArgumentException('Invalid identifier');
		}

		$query = $this->alertModel->query();

		return $query->with('organisation')->findOrFail((int)$id);
	}
}
