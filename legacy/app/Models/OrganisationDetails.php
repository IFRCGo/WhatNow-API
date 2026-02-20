<?php

namespace App\Legacy\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationDetails extends Model
{
	public $timestamps = false;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'legacy_organisation_details';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'org_id',
		'language_code',
		'org_name',
		'attribution_message',
		'published'
	];

	/**
	 * The attributes that should be casted to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'published' => 'boolean',
	];

	public function organisation()
	{
		return $this->belongsTo('App\Legacy\Models\Organisation', 'org_id', 'id');
	}
}

