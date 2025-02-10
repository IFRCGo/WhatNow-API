<?php

namespace App\Classes\Repositories;

use App\Models\Region;
use App\Models\WhatNowEntity;
use Illuminate\Support\Facades\Log;

class WhatNowRepository implements WhatNowRepositoryInterface
{
    public const EVENT_STAGES = [
        'warning',
        'immediate',
        'recover',
        'anticipated',
        'assess_and_plan',
        'mitigate_risks',
        'prepare_to_respond',

        
    ];

    /**
     * @var WhatNowEntity
     */
    protected $whatNowModel;

    /** @var WhatNowTranslationRepositoryInterface */
    protected $whatNowTransRepo;

    /**
     * @param WhatNowEntity $whatNowModel
     * @param WhatNowTranslationRepositoryInterface $whatNowTransRepo
     */
    public function __construct(WhatNowEntity $whatNowModel, WhatNowTranslationRepositoryInterface $whatNowTransRepo)
    {
        $this->whatNowModel = $whatNowModel;
        $this->whatNowTransRepo = $whatNowTransRepo;
    }

    /**
     * @param array $attributes
     * @return WhatNowEntity
     */
    public function newInstance(array $attributes = [])
    {
        return $this->whatNowModel->newInstance($attributes);
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        return $this->whatNowModel->all($columns);
    }

    /**
     * @param array $attributes
     * @return WhatNowEntity
     */
    public function create(array $attributes)
    {
        /** @var WhatNowEntity $entity */
        $entity = new $this->whatNowModel([
            'org_id' => $attributes['org_id'],
            'region_id' => empty($attributes['region_id']) ? null : $attributes['region_id'],
            'country_code' => $attributes['country_code'],
            'event_type' => $attributes['event_type'],
        ]);
        $entity->save();

        return $entity;
    }

    /**
     * @param array $input
     * @return WhatNowEntity
     */
    public function createFromArray(array $input)
    {
        $entity = $this->create([
            'org_id' => $input['orgId'],
            'region_id' => data_get($input, 'region_id', null),
            'country_code' => $input['countryCode'],
            'event_type' => $input['eventType'],
        ]);

        if ($input['translations']) {
            $this->whatNowTransRepo->addTranslations($entity, $input['translations']);
        }

        return $entity;
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->whatNowModel->findOrFail($id, $columns);
    }

    /**
     * @param $id
     * @param array $input
     * @return mixed
     */
    public function updateWithIdAndInput($id, array $input)
    {
        /** @var WhatNowEntity $entity */
        $entity = $this->whatNowModel->findOrFail($id);

        Log::info('Updating WhatNowEntity', [
            'id' => $id,
            'input' => $input,
        ]);

        $entity->update([
            'org_id' => $input['orgId'],
            'region_id' => data_get($input, 'region_id', null),
            'country_code' => $input['countryCode'],
            'event_type' => $input['eventType'],
        ]);

        if ($input['translations']) {
            Log::info('Updating WhatNowEntity translations', [
                'translations' => $input['translations'],
            ]);

            $this->whatNowTransRepo->addTranslations($entity, $input['translations']);
        }

        return $entity;
    }

    /**
     * @param $id
     * @return int
     */
    public function destroy($id)
    {
        return $this->whatNowModel->destroy($id);
    }

    /**
     * @param $orgId
     * @param null $lang
     * @param array $eventTypes
     */
    public function findItemsForOrgId($orgId, $lang = null, array $eventTypes = [], $regId = null)
    {
        $query = $this->whatNowModel->where('org_id', $orgId);

        if (count($eventTypes)) {
            $query->whereIn('event_type', $eventTypes);
        }

        if ($regId) {
            $query->where('region_id', $regId);
        }

        return $query->get();
    }

    /**
     * @param $orgId
     * @param null $lang
     * @param array $eventTypes
     */
    public function findItemsForRegionByOrgId($orgId, $lang = null, array $eventTypes = [], $regionName = null)
    {
        $query = $this->whatNowModel->where('org_id', $orgId);

        if (count($eventTypes)) {
            $query->whereIn('event_type', $eventTypes);
        }

        if (empty($regionName) || strtolower($regionName) == 'national') {
            $query->whereNull('region_id');
        } else {
            $region = Region::where('organisation_id', '=', $orgId)
                ->where('title', '=', $regionName)
                ->first();

            if (! empty($region)) {
                $query->where('region_id', $region->id);
            }
        }

        return $query->get();
    }
}
