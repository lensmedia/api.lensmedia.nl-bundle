<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Coords;
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
    /** @deprecated use AddressType enum instead */
    public const string DEFAULT = AddressType::Default->value;

    /** @deprecated use AddressType enum instead */
    public const string MAILING = AddressType::Mailing->value;

    /** @deprecated use AddressType enum instead */
    public const string SHIPPING = AddressType::Shipping->value;

    /** @deprecated use AddressType enum instead */
    public const string BILLING = AddressType::Billing->value;

    /** @deprecated use AddressType enum instead */
    public const string OPERATING = AddressType::Operating->value;

    /** @deprecated see AddressType enum and use cases() instead */
    public const array TYPES = [
        AddressType::Default->value => AddressType::Default->value,
        AddressType::Mailing->value => AddressType::Mailing->value,
        AddressType::Shipping->value => AddressType::Shipping->value,
        AddressType::Billing->value => AddressType::Billing->value,
        AddressType::Operating->value => AddressType::Operating->value,
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
    public string $type = AddressType::Default->value;

    #[ORM\Column(type: 'decimal', precision: Coords::PRECISION_1M + 3, scale: Coords::PRECISION_1M, nullable: true)]
    public ?string $longitude = null;

    #[ORM\Column(type: 'decimal', precision: Coords::PRECISION_1M + 2, scale: Coords::PRECISION_1M, nullable: true)]
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
        $this->personal = $personal;
        $personal?->addAddress($this);
    }

    public function setCompany(?Company $company): void
    {
        if ($this->company === $company) {
            return;
        }

        $this->company?->removeAddress($this);
        $this->company = $company;
        $company?->addAddress($this);
    }

    public function isDefault(): bool
    {
        return AddressType::Default->value === $this->type;
    }

    public function isMailing(): bool
    {
        return AddressType::Mailing->value === $this->type;
    }

    public function isShipping(): bool
    {
        return AddressType::Shipping->value === $this->type;
    }

    public function isBilling(): bool
    {
        return AddressType::Billing->value === $this->type;
    }

    public function isOperating(): bool
    {
        return AddressType::Operating->value === $this->type;
    }

    public function isLocatedAtTheSamePlaceAs(self $address): bool
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
