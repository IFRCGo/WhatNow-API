<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class WhatNowEntityStage extends Model
{
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
        'urgency_id'

    ];

    /**
     * Model validation rules
     *
     * @var array
     */
    protected $rules = [
        'language_code' => 'required|string|between:2,10',
        'urgency_id' => 'required|integer',
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
