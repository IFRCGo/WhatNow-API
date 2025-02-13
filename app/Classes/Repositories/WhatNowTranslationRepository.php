<?php

namespace App\Classes\Repositories;

use App\Models\WhatNowEntity;
use App\Models\WhatNowEntityTranslation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatNowTranslationRepository implements WhatNowTranslationRepositoryInterface
{
    /**
     * @var WhatNowEntityTranslation
     */
    protected $whatNowTranslationModel;

    /** @var KeyMessageRepositoryInterface */
    protected $keyMessageRepository;

    /** @var SupportingMessageRepositoryInterface */
    protected $supportingMessageRepository;

    /**
     * @param WhatNowEntityTranslation $whatNowTranslationModel
     */
    public function __construct(WhatNowEntityTranslation $whatNowTranslationModel, KeyMessageRepositoryInterface $keyMessageRepository, SupportingMessageRepositoryInterface $supportingMessageRepository)
    {
        $this->whatNowTranslationModel = $whatNowTranslationModel;
        $this->keyMessageRepository = $keyMessageRepository;
        $this->supportingMessageRepository = $supportingMessageRepository;
    }

    /**
     * @param array $attributes
     * @return WhatNowEntityTranslation
     */
    public function newInstance(array $attributes = [])
    {
        return $this->whatNowTranslationModel->newInstance($attributes);
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        return $this->whatNowTranslationModel->all($columns);
    }

    /**
     * @param WhatNowEntity $entity
     * @param array $translations
     */
    public function addTranslations(WhatNowEntity $entity, array $translations)
    {
        try {
            foreach ($translations as $trans) {
                Log::info('Adding translation', [
                    'trans' => $trans,
                ]);

                $translation = $entity->translations()->create([
                    'web_url' => $trans['webUrl'],
                    'language_code' => $trans['lang'],
                    'title' => $trans['title'],
                    'description' => isset($trans['description']) ? $trans['description'] : null,
                ]);

                foreach ($trans['stages'] as $stage => $content) {
                    if (! in_array($stage, WhatNowRepository::EVENT_STAGES)) {
                        continue;
                    }

                    $transStage = $translation->stages()
                        ->where('language_code', '=',  $trans['lang'])
                        ->where('stage', '=', $stage)
                        ->first();

                    if (empty($transStage)) {
                        if (! empty($content)) {
                            try {
                                $stage = $translation->stages()->create([
                                    'language_code' => $trans['lang'],
                                    'stage' => $stage,
                                ]);
                                foreach ($content as $message) {
                                    $keyMessage = $this->keyMessageRepository->create([
                                        'entities_stage_id' => $stage->id,
                                        'title' => $message['title'], 
                                    ]);
                                    
                                    foreach ($message['content'] as $supportMessage) {
                                        $this->supportingMessageRepository->create([
                                            'key_message_id' => $keyMessage->id,
                                            'content' => $supportMessage,
                                        ]);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('ERROR', [$e->getMessage()]);
                            }
                        }
                    } else {
                        $transStage->update([
                            'content' => json_encode($content),
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('ERROR', [$e->getMessage()]);
        }
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->whatNowTranslationModel->findOrFail($id, $columns);
    }

    /**
     * Returns latest translations for an entity
     *
     * Query orders translations with this entity id by their created date, and then groups them together
     * by the language code, so that only the latest translation for each language is returned.
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestTranslations($id)
    {
        $model = $this->whatNowTranslationModel;

        return $model::fromQuery(DB::raw('SELECT wet1.*
FROM whatnow_entity_translations wet1
LEFT JOIN whatnow_entity_translations wet2 ON wet1.entity_id = wet2.entity_id AND wet1.language_code = wet2.language_code AND wet1.created_at < wet2.created_at
WHERE wet2.created_at IS NULL AND wet1.entity_id = :entityId'), ['entityId' => $id]);
    }

    /**
     * Returns latest published translations for an entity
     *
     * Query orders translations with this entity id by their published date, and then groups them together
     * by the language code, so that only the latest published translation for each language is returned.
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLatestPublishedTranslations($id, $lang = null)
    {
        $model = $this->whatNowTranslationModel;

        $query = 'SELECT wet1.*
              FROM whatnow_entity_translations wet1
              LEFT JOIN whatnow_entity_translations wet2 ON wet1.entity_id = wet2.entity_id AND wet1.language_code = wet2.language_code AND wet1.published_at < wet2.published_at
              WHERE wet2.published_at IS NULL AND wet1.entity_id = :entityId AND wet1.published_at IS NOT NULL';
        
        $bindings = ['entityId' => $id];

        if ($lang !== null) {
            $query .= ' AND wet1.language_code = :lang';
            $bindings['lang'] = $lang;
        }

        return $model::fromQuery(DB::raw($query), $bindings);
    }

    /**
     * @param array $ids
     * @returns void
     */
    public function publishTranslationsById(array $ids)
    {
        WhatNowEntityTranslation::whereIn('id', $ids)->update(['published_at' => new Carbon()]);
    }

    /**
     * @param int $id
     * @return WhatNowEntityTranslation
     */
    public function getTranslationById($id)
    {
        return WhatNowEntityTranslation::findOrFail($id);
    }

    /**
     * @param $id
     * @return int
     */
    public function destroy($id)
    {
        return $this->whatNowTranslationModel->destroy($id);
    }

    public function create(array $attributes)
    {
        // not implemented
    }

    public function updateWithIdAndInput($id, array $input)
    {
        // not implemented
    }
}
