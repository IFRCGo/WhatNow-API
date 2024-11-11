<?php

namespace App\Classes\Serializers;

use League\Fractal\Serializer\DataArraySerializer;

/**
 * Based on https://github.com/thephpleague/fractal/issues/90#issuecomment-54203967
 *
 * Wraps the response in a 'data' key at the root, but does not for named resources.
 * Allows for included transformers without breaking backwards compatibility.
 */
class CustomDataSerializer extends DataArraySerializer
{
    public function collection(?string $resourceKey, array $data): array
    {
        return ($resourceKey && $resourceKey !== 'data') ? [$resourceKey => $data] : $data;
    }

    public function item(?string $resourceKey, array $data): array
    {
        return ($resourceKey && $resourceKey !== 'data') ? [$resourceKey => $data] : $data;
    }

    public function null(): ?array
    {
        return ['data' => []];
    }
}
