<?php

namespace App\Models;

use App\Classes\Cap\CapEntityInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Alert extends Model implements CapEntityInterface
{
    /**
     * @var
     */
    protected $errors;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'area_polygon' => 'array',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'sent_date',
        'onset_date',
        'effective_date',
        'expiry_date',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'org_id',
        'country_code',
        'language_code',
        'event',
        'headline',
        'description',
        'area_polygon',
        'area_description',
        'type',
        'status',
        'scope',
        'category',
        'urgency',
        'severity',
        'certainty',
        'sent_date',
        'onset_date',
        'effective_date',
        'expiry_date',
    ];

    /**
     * Model validation rules
     *
     * @var array
     */
    protected $rules = [
        'country_code' => 'required|string|between:2,10',
        'language_code' => 'string|between:2,10',
        'event' => 'required|string|max:120',
        'headline' => 'required|string|max:512',
        'description' => 'required|string|max:10000',
        'area_polygon' => 'required|array',
        'area_description' => 'required|string|max:512',
        'type' => 'required|in:alert,update,cancel,ack,error',
        'status' => 'required|in:actual,system,test,draft',
        'scope' => 'in:public,restricted,private',
        'category' => 'required|in:geo,met,safety,security,rescue,fire,health,env,transport,infra,CBRNE,other',
        'urgency' => 'in:immediate,expected,future,past,unknown',
        'severity' => 'in:extreme,severe,moderate,minor,unknown',
        'certainty' => 'in:observed,likely,possible,unlikely,unknown',
        'sent_date' => 'required|date',
        'onset_date' => 'date',
        'effective_date' => 'date',
        'expiry_date' => 'date',
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

    public function setSentDateAttribute($date)
    {
        $this->attributes['sent_date'] = (is_null($date) ? null : new Carbon($date));
    }

    public function setOnsetDateAttribute($date)
    {
        $this->attributes['onset_date'] = (is_null($date) ? null : new Carbon($date));
    }

    public function setEffectiveDateAttribute($date)
    {
        $this->attributes['effective_date'] = (is_null($date) ? null : new Carbon($date));
    }

    public function setExpiryDateAttribute($date)
    {
        $this->attributes['expiry_date'] = (is_null($date) ? null : new Carbon($date));
    }

    /**
     * Returns polygon data as a string for use in CAP XML
     *
     * @return string
     */
    public function getAreaPolygonString()
    {
        if (is_array($this->area_polygon) && array_key_exists('coordinates', $this->area_polygon)) {
            $points = array_map(function ($pair) {
                return implode(',', [number_format($pair[1], 2, '.', ''), number_format($pair[0], 2, '.', '')]);
            }, $this->area_polygon['coordinates'][0]);

            // reverse array so that cap polygon string is counter-clockwise
            return implode(' ', array_reverse($points));
        }

        return null;
    }

    /**
     * @param string $extension
     * @return string
     */
    public function getFilePath($extension = 'xml')
    {
        return sprintf(
            '%s/%s/%s.%s.%d.' . $extension,
            $this->created_at->format('Y-m-d'),
            $this->organisation->oid_code,
            $this->organisation->oid_code,
            $this->created_at->format('Y'),
            $this->id
        );
    }

    /**
     * Returns a URL path for this model in the format
     * 2016-12-23/urn:oid:2.49.1.24/urn:oid:2.49.1.24.2016.2000.xml
     *
     * @return string
     */
    public function getXmlPath()
    {
        return $this->getFilePath('xml');
    }

    /**
     * @return string
     */
    public function getHtmlPath()
    {
        return $this->getFilePath('html');
    }

    /**
     * Returns a unique identifier string for this alert
     *
     * @return string
     */
    public function getCapIdentifier()
    {
        return sprintf(
            '%s.%s.%d',
            $this->organisation->oid_code,
            $this->created_at->format('Y'),
            $this->id
        );
    }

    /**
     * Returns fully qualified URL for this object
     *
     * @todo move environment condition out of this class
     * @param string $extension
     * @return string
     */
    public function getPublicUrl($extension = 'xml')
    {
        $filepath = $this->getFilePath($extension);

        if (app()->environment('production')) {
            //return valid url
            return url(config('app.cdn_host') . config('app.cdn_alert_path') . '/' . $filepath);
        }

        return url(config('app.url') . config('app.cdn_alert_path') . '/' . $filepath);
    }

    public function getSender()
    {
        return str_replace(' ', '_', $this->organisation->org_name);
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

    public function organisation()
    {
        return $this->belongsTo('App\Models\Organisation', 'org_id', 'id');
    }
}
