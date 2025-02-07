<?php
namespace App\Classes\Repositories;

use App\Models\Region;


class RegionRepository implements RegionRepositoryInterface
{
    /**
     * @var Region
     */
    protected $regModel;

    /**
     * @param Region $regionModel
     */
    public function __construct(Region $regModel)
    {
        $this->regModel = $regModel;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function newInstance(array $attributes = [])
    {
        return $this->regModel->newInstance($attributes);
    }


    public function findBySlug($orgId, $slug)
    {
        $slug = strtolower($slug);
        $slug = str_replace(' ', '-', $slug);
        return $this->regModel->where('slug', $slug)->where('organisation_id', $orgId)->firstOrFail();;
    }

    public function mapTranslationInput($regionId, $language_code, $data)
    {
        return [
            'region_id' => $regionId,
            'language_code' => $language_code,
            'title' => $data['title'],
            'slug' => str_slug($data['title']),
            'description' => $data['description'],
        ];
    }

}
