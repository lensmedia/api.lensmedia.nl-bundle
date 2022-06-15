<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Address;
use Lens\Bundle\LensApiBundle\Data\Company;
use Symfony\Component\Uid\Ulid;

class AddressRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('addresses.json', $options)->toArray();

        return $this->api->asArray($response, Company::class);
    }

    public function byId(string|Ulid $address): Address
    {
        $data = $this->api->get(sprintf(
            'addresses/%s.json',
            (string)$address,
        ))->toArray();

        return $this->api->as($data, Address::class);
    }
}
