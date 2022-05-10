<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;

class Address
{
    public const DEFAULT = 'default';
    public const MAILING = 'mailing';
    public const BILLING = 'billing';
    public const SHIPPING = 'shipping';
    public const OPERATING = 'operating';

    public Ulid $id;

    public string $type;

    public string $streetName;

    public int $streetNumber;

    public ?string $addition = null;

    public string $zipCode;

    public string $city;

    public string $country = 'NL';

    public ?string $longitude = null;

    public ?string $latitude = null;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
