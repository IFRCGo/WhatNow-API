<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class SupportingMessage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'supporting_messages';

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
     * @var array
     */
    protected $fillable = ['key_message_id', 'content'];

    /**
     * Model validation rules
     * @var array
     */
    protected $rules = [
        'key_message_id' => 'required|integer',
        'content' => 'required|string|between:2,1000',
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

    public function keyMessage()
    {
        return $this->belongsTo(KeyMessage::class, 'key_message_id');
    }
}
