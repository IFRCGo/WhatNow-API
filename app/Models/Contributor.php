<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Contributor extends Model
{

    /**
     * table
     *
     * @var string
     */
    protected $table = 'contributors';


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
    protected $fillable = ['name', 'logo', 'org_detail_id'];

    /**
     * Model validation rules
     * @var array
     */
    protected $rules = [
        'name' => 'required|string|between:2,100',
        'logo' => 'string|between:2,100',
        'org_id' => 'required|integer',
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

    public function organisation()
    {
        return $this->belongsTo(OrganisationDetails::class, 'org_detail_id');
    }

}
