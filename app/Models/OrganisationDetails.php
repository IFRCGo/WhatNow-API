<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationDetails extends Model
{
	public $timestamps = false;

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
		return $this->belongsTo('App\Models\Details', 'org_id', 'id');
	}

	public function contributors()
	{
		return $this->hasMany(Contributor::class, 'org_detail_id');
	}
}

