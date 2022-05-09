<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Address;
use Symfony\Component\Validator\Constraints\Ulid;

class AddressRepository extends AbstractRepository
{
    public function byId(string|Ulid $address): Address
    {
        $data = $this->api->get(sprintf(
            'addresses/%s.json',
            (string)$address,
        ))->toArray();

        return $this->api->as($data, Address::class);
    }
}
