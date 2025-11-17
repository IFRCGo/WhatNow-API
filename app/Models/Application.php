<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'applications';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'tenant_user_id',
        'name',
        'description',
        'estimated_users_count',
        'key',
        'is_active',
    ];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active applications
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * A sure method to generate a unique API key
     *
     * @return string
     */
    public static function generateKey()
    {
        do {
            $newKey = \Illuminate\Support\Str::random(32);
        } // Already in the DB? Fail. Try again
        while (self::keyExists($newKey));

        return $newKey;
    }

    /**
     * Checks whether a key exists in the database or not
     *
     * @param $key
     * @return bool
     */
    private static function keyExists($key)
    {
        $apiKeyCount = self::where('key', '=', $key)->limit(1)->count();
        if ($apiKeyCount > 0) {
            return true;
        }

        return false;
    }
}
