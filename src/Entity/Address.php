<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Serializer\AutoContextBuilder;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\AddressInterface;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\AddressRepository;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: ['get', 'patch', 'delete'],
    subresourceOperations: [
        'api_companies_addresses_get_subresource' => [
            'normalization_context' => [
                'groups' => ['company'],
            ],
        ],
        'api_driving_schools_addresses_get_subresource' => [
            'normalization_context' => [
                'groups' => ['driving_school'],
            ],
        ],
        'api_personals_addresses_get_subresource' => [
            'normalization_context' => [
                'groups' => ['personal'],
            ],
        ],
    ],
    denormalizationContext: [
        AutoContextBuilder::DISABLE => true,
    ],
    normalizationContext: [
        'groups' => ['address'],
    ],
)]
#[ORM\Index(fields: ['streetName'])]
#[ORM\Index(fields: ['streetNumber'])]
#[ORM\Index(fields: ['city'])]
#[ORM\Index(fields: ['zipCode'])]
class Address
{
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
    public string $type = AddressInterface::DEFAULT;

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
}
