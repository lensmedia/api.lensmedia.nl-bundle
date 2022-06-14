<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Address
{
    public const DEFAULT = 'default';
    public const MAILING = 'mailing';
    public const BILLING = 'billing';
    public const SHIPPING = 'shipping';
    public const OPERATING = 'operating';
    public const TYPES = [
        self::DEFAULT => self::DEFAULT,
        self::MAILING => self::MAILING,
        self::BILLING => self::BILLING,
        self::SHIPPING => self::SHIPPING,
        self::OPERATING => self::OPERATING,
    ];

    #[Assert\NotBlank(message: 'address.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'address.type.not_blank')]
    #[Assert\Choice(choices: self::TYPES, message: 'address.type.choice')]
    public string $type = self::DEFAULT;

    #[Assert\NotBlank(message: 'address.street_name.not_blank')]
    public string $streetName;

    #[Assert\NotBlank(message: 'address.street_number.not_blank')]
    public int $streetNumber;

    public ?string $addition = null;

    #[Assert\NotBlank(message: 'address.zip_code.not_blank')]
    public string $zipCode;

    #[Assert\NotBlank(message: 'address.city.not_blank')]
    public string $city;

    #[Assert\NotBlank(message: 'address.country.not_blank')]
    #[Assert\Country(message: 'address.country.country')]
    public string $country = 'NL';

    #[Assert\Range(
        minMessage: 'address.longitude.range.min',
        maxMessage: 'address.longitude.range.max',
        min: Coords::LONGITUDE_MIN,
        max: Coords::LONGITUDE_MAX,
    )]
    public ?string $longitude = null;

    #[Assert\Range(
        minMessage: 'address.latitude.range.min',
        maxMessage: 'address.latitude.range.max',
        min: Coords::LATITUDE_MIN,
        max: Coords::LATITUDE_MAX,
    )]
    public ?string $latitude = null;
}
