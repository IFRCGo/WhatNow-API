<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class WhatNowEntityTranslation extends Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'whatnow_entity_translations';

	/**
	 * @var
	 */
	protected $errors;

	/**
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'published_at'
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
        'entity_id',
		'web_url',
		'language_code',
		'title',
		'description',
		'created_at',
		'published_at'
	];

	/**
	 * Model validation rules
	 *
	 * @var array
	 */
	protected $rules = [
		'language_code' => 'required|string|between:2,10',
		'title' => 'string|max:255',
		'description' => 'string',
		'web_url' => 'url',
	];

	public static function boot()
	{
		parent::boot();

		static::creating(function($model)
		{
			$model->created_at = Carbon::now();
		});
	}

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

//    public function getRegionNameAttribute()
//    {
//        if(empty($this->entity->subnational)){
//            return '';
//        }
//
//        return $this->entity->subnational->title;
//    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\belongsTo
	 */
	public function entity()
	{
		return $this->belongsTo('App\Models\WhatNowEntity', 'entity_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function stages()
	{
		return $this->HasMany('App\Models\WhatNowEntityStage', 'translation_id', 'id');
	}

	/**
	 * @param \DateTimeInterface|null $dateTime
	 * @return WhatNowEntityTranslation
	 */
	public function publish(\DateTimeInterface $dateTime = null)
	{
		if(is_null($dateTime)){
			$dateTime = new Carbon();
		}

		$this->published_at = $dateTime;
		$this->save();

		return $this;
	}

	/**
	 * @return WhatNowEntityTranslation
	 */
	public function revert()
	{
		$this->published_at = null;
		$this->save();

		return $this;
	}
}

