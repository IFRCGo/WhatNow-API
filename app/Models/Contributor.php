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
        'logo' => 'string|between:2,2048',
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

    public function getLogoPath()
	{
		return '/' . $this->logo;
	}

    public function getLogoImageUrl()
	{
		$filepath = $this->getLogoPath();

		if (app()->environment('production')) {

			//return valid url
			return url(('https://'). config('app.bucket_name') . '.' . config('app.bucket_domain') . '/' . config('app.bucket_container') . $filepath);
		}
		//TODO configure for QA environment
		return url(('https://'). config('app.bucket_name') . '.' . config('app.bucket_domain') . '/' . config('app.bucket_container') . $filepath);
	}

}
