<?php

namespace App\Classes\Transformers;

use App\Models\Application;
use League\Fractal\TransformerAbstract;

class ApplicationTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @param Application $model
     * @return array
     */
    public function transform(Application $model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'description' => $model->description,
            'estimatedUsers' => $model->estimated_users_count,
            'key' => $model->key,
        ];
    }
}
