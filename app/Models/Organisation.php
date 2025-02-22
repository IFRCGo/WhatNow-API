<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'organisations';

	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'org_name',
		'oid',
		'attribution_url',
		'attribution_file_name'
	];

	public function alerts()
	{
		return $this->hasMany('App\Models\Alert', 'org_id');
	}

	public function details()
	{
		return $this->hasMany('App\Models\OrganisationDetails', 'org_id');
	}

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

	public function getAttributionFilePath()
	{
		return '/' . $this->attribution_file_name;
	}

	public function getAttributionImageUrl()
	{
		$filepath = $this->getAttributionFilePath();

		if (app()->environment('production')) {

			//return valid url
			return url(('https://'). config('app.bucket_name') . '.' . config('app.bucket_domain') . '/' . config('app.bucket_container') . $filepath);
		}
		//TODO configure for QA environment
		return url(('https://'). config('app.bucket_name') . '.' . config('app.bucket_domain') . '/' . config('app.bucket_container') . $filepath);
	}
}

