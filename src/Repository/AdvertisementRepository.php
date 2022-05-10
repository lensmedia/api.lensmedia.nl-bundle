<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Advertisement;

class AdvertisementRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get(
            'advertisements.json',
            $options,
        )->toArray();

        return $this->api->asArray($response, Advertisement::class);
    }
}
