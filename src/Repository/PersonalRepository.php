<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Personal;
use Symfony\Component\Validator\Constraints\Ulid;

class PersonalRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get(
            'personals.json',
            $options,
        )->toArray();

        return $this->api->asArray($response, Personal::class);
    }

    public function byId(Ulid|string $personal): ?Personal
    {
        $response = $this->api->get(sprintf(
            'personals/%s.json',
            $personal,
        ))->toArray();

        return $this->api->as(
            $response,
            Personal::class,
        );
    }
}
