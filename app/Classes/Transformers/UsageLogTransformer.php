<?php

namespace App\Classes\Transformers;

use App\Models\Application;
use App\Models\UsageLog;
use League\Fractal\TransformerAbstract;

class UsageLogTransformer extends TransformerAbstract
{
    /**
     * Turn this item object into a generic array
     *
     * @param Application $model
     * @return array
     */
    public function transform(UsageLog $model)
    {
        return [
            'id' => $model->id,
            'application_id' => $model->application_id,
            'endpoint' => $model->endpoint,
            'method' => $model->method,
            'timestamp' => $model->timestamp,
        ];
    }
}
