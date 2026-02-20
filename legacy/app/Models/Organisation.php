<?php

namespace App\Legacy\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'legacy_organisations';

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
		return $this->hasMany('App\Legacy\Models\OrganisationDetails', 'org_id');
	}

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

	public function getAttributionImageUrl()
	{
		return 'https://smdbstorageaccount.blob.core.windows.net/whatnow-assets/attribution_images/' . $this->attribution_file_name;
	}
}
