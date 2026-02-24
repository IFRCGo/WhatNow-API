<?php

namespace App\Legacy\Classes\Transformers;

use League\Fractal\TransformerAbstract;
use App\Legacy\Models\Organisation;

class OrganisationTransformer extends TransformerAbstract
{
    /**
     * @var bool
     */
    private $unpublished = false;

    /**
     * @param array $configuration
     */
    public function __construct($configuration = [])
    {
        if (isset($configuration['unpublished']) && is_bool($configuration['unpublished'])) {
            $this->unpublished = $configuration['unpublished'];
        }
    }

    /**
     * Turn this item object into a generic array
     *
     * @param Organisation $model
     * @return array
     */
    public function transform(Organisation $model)
    {
        $response = [
            'countryCode'  => $model->country_code,
            'name'         => $model->org_name,
            'url'          => $model->attribution_url,
            'imageUrl'     => $model->attribution_file_name ? $model->getAttributionImageUrl() : null,
            'translations' => null,
        ];

        if ($model->details->count()) {
            $response['translations'] = [];
            foreach ($model->details as $detail) {
                if ($this->unpublished || $detail->published) {
                    $response['translations'][$detail->language_code] = [
                        'languageCode'       => $detail->language_code,
                        'name'               => $detail->org_name,
                        'attributionMessage' => $detail->attribution_message,
                        'published'          => (bool) $detail->published,
                    ];
                }
            }
        }

        return $response;
    }
}

