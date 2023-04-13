<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\AddressRepository;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ORM\Index(fields: ['streetName'])]
#[ORM\Index(fields: ['streetNumber'])]
#[ORM\Index(fields: ['city'])]
#[ORM\Index(fields: ['zipCode'])]
class Address
{
    public const DEFAULT = 'default';
    public const MAILING = 'mailing';
    public const SHIPPING = 'shipping';
    public const BILLING = 'billing';
    public const OPERATING = 'operating';

    public const TYPES = [
        self::DEFAULT => self::DEFAULT,
        self::MAILING => self::MAILING,
        self::SHIPPING => self::SHIPPING,
        self::BILLING => self::BILLING,
        self::OPERATING => self::OPERATING,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column]
    public string $streetName;

    #[ORM\Column]
    public int $streetNumber;

    #[ORM\Column(nullable: true)]
    public ?string $addition = null;

    #[ORM\Column]
    public string $zipCode;

    #[ORM\Column]
    public string $city;

    #[ORM\Column(length: 2)]
    #[Assert\Country]
    public string $country = 'NL';

    #[ORM\Column]
    public string $type = self::DEFAULT;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 5, nullable: true)]
    public ?string $longitude = null;

    #[ORM\Column(type: 'decimal', precision: 7, scale: 5, nullable: true)]
    public ?string $latitude = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'addresses')]
    public ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: Personal::class, inversedBy: 'addresses')]
    public ?Personal $personal = null;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public function setPersonal(?Personal $personal): void
    {
        if ($this->personal === $personal) {
            return;
        }

        $this->personal?->removeAddress($this);
        $personal?->addAddress($this);
        $this->personal = $personal;
    }

    public function setCompany(?Company $company): void
    {
        if ($this->company === $company) {
            return;
        }

        $this->company?->removeAddress($this);
        $company?->addAddress($this);
        $this->company = $company;
    }

    public function isDefault(): bool
    {
        return self::DEFAULT === $this->type;
    }

    public function isMailing(): bool
    {
        return self::MAILING === $this->type;
    }

    public function isShipping(): bool
    {
        return self::SHIPPING === $this->type;
    }

    public function isBilling(): bool
    {
        return self::BILLING === $this->type;
    }

    public function isOperating(): bool
    {
        return self::OPERATING === $this->type;
    }

    public function isLocatedAtTheSamePlaceAs(Address $address): bool
    {
        return $this->streetName === $address->streetName
            && $this->streetNumber === $address->streetNumber
            && $this->addition === $address->addition
            && $this->zipCode === $address->zipCode
            && $this->city === $address->city
            && $this->country === $address->country;
    }

    public function __clone(): void
    {
        $this->id = new Ulid();
    }
}
