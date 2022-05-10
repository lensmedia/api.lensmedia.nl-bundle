<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\DriversLicense;

class DriversLicenseRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get(
            'drivers-licences.json',
            $options,
        )->toArray();

        return $this->api->asArray($response, DriversLicense::class);
    }
}
