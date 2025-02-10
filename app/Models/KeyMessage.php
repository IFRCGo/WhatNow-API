<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class KeyMessage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'key_messages';

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
    protected $fillable = ['entities_stage_id', 'title'];

    /**
     * Model validation rules
     *
     * @var array
     */
    protected $rules = [
        'entities_stage_id' => 'required|integer',
        'title' => 'required|string|between:2,255',
    ];

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

    public function entityStage()
    {
        return $this->belongsTo(WhatnowEntityStage::class, 'entities_stage_id');
    }

    public function supportingMessages()
    {
        return $this->hasMany(SupportingMessage::class, 'key_message_id');
    }
}
