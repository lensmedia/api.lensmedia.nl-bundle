<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Company;
use Symfony\Component\Validator\Constraints\Ulid;

class DrivingSchoolRepository extends AbstractRepository
{
    public function byId(string|Ulid $drivingSchool): Company
    {
        $response = $this->api->get(sprintf(
            'driving-schools/%s.json',
            (string)$drivingSchool,
        ))->toArray();

        return $this->api->as($response, Company::class);
    }

    public function byChamberOfCommerce(string $chamberOfCommerce): ?Company
    {
        $response = $this->api->get('driving-schools.json', [
                'query' => ['chamberOfCommerce' => $chamberOfCommerce],
            ])->toArray()[0] ?? null;

        if (!$response) {
            return null;
        }

        return $this->byId($response['id']);
    }

    public function search(string $terms): array
    {
        $response = $this->api->get('driving-schools/search.json', [
            'query' => [
                'q' => $terms,
            ],
        ])->toArray();

        return $this->api->asArray($response, Company::class);
    }

    public function nearby(string|Ulid $drivingSchool): array
    {
        $response = $this->api->get(sprintf(
            'driving-schools/%s/nearby.json',
            $drivingSchool,
        ))->toArray();

        return $this->api->asArray($response, Company::class);
    }
}
