<?php

namespace App\Classes\Repositories;

class RegionRepository implements RegionRepositoryInterface
{

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
