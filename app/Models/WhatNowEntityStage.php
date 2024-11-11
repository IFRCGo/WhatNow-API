<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class WhatNowEntityStage extends Model
{
	public static $stages = [
		'mitigation',
		'seasonalForecast',
		'watch',
		'warning',
		'immediate',
		'recover'
	];

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'whatnow_entity_stages';

	/**
	 * @var
	 */
	protected $errors;

	/**
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
        'translation_id',
		'language_code',
		'stage',
		'content'
	];

	/**
	 * Model validation rules
	 *
	 * @var array
	 */
	protected $rules = [
		'language_code' => 'required|string|between:2,10',
		'stage' => 'string|max:10',
		'content' => 'string'
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

	public function translation()
	{
		return $this->belongsTo('App\Models\WhatNowEntityTranslation', 'translation_id', 'id');
	}
}

