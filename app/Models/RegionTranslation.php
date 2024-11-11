<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionTranslation extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'region_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'region_id',
        'title',
        'language_code',
        'description',
        'language_code'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];


    public function region()
    {
        return $this->belongsTo(Region::class);
    }


}
