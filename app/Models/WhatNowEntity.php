<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WhatNowEntity extends Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'whatnow_entities';

	/**
	 * @var
	 */
	protected $errors;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at'
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
        'org_id',
        'region_id',
		'event_type',
	];

	/**
	 * Model validation rules
	 *
	 * @var array
	 */
	protected $rules = [
		'event_type' => 'required|numeric',
	];

	/**
	 * @param $data
	 * @return bool
	 */
	public function validate($data)
	{
		$v = Validator::make($data, $this->rules);

		if ($v->fails()) {
			$this->errors = $v->errors();

			return false;
		}

		return true;
	}

	/**
	 * @return mixed
	 */
	public function errors()
	{
		return $this->errors;
	}

	/**
	 * Returns name of related org
	 *
	 * @return string
	 */
	public function getSenderName()
	{
		return $this->organisation->org_name;
	}


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function organisation()
	{
		return $this->belongsTo('App\Models\Organisation', 'org_id', 'id');
	}


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getRegionNameAttribute()
    {
        if(empty($this->region)){
            return 'National';
        }

        return $this->region->title;
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function translations()
	{
		return $this->hasMany('App\Models\WhatNowEntityTranslation', 'entity_id', 'id');
	}
}

