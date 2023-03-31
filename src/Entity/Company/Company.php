<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\DateFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\DataFilters\Old\DealerFilter;
use App\DataFilters\Old\EmailFilter;
use App\DataFilters\Old\PlainPasswordFilter;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\PaymentMethod;
use Lens\Bundle\LensApiBundle\Entity\User;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Lens\Bundle\LensApiBundle\ContactMethod;
use Lens\Bundle\LensApiBundle\Address;
use Lens\Bundle\LensApiBundle\Remark;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type')]
#[ORM\DiscriminatorMap(Company::TYPE_TO_CLASS)]
#[ApiResource(
    collectionOperations: [
        'get',
        self::SEARCH_OPERATION => [
            'method' => 'GET',
            'path' => '/companies/search.{_format}',
            'openapi_context' => [
                'description' => 'Search for any company looking through multiple fields.',
                'parameters' => [
                    [
                        'name' => 'q',
                        'in' => 'query',
                        'description' => 'The search term(s) to look for.',
                        'type' => 'string',
                        'required' => true,
                    ],
                ],
            ],
        ],
        self::CHAMBER_OF_COMMERCE_TO_ID_OPERATION => [
            'method' => 'GET',
            'path' => '/companies/chamber-of-commerce-to-id.{_format}',
        ],
    ],
    itemOperations: ['get'],
    subresourceOperations: [
        'api_dealers_companies_get_subresource' => [
            'normalization_context' => [
                'groups' => ['dealer'],
            ],
        ],
    ],
    denormalizationContext: [
        'groups' => ['company'],
    ],
    normalizationContext: [
        'groups' => ['company'],
    ],
)]
#[ApiFilter(PlainPasswordFilter::class)]
#[ApiFilter(EmailFilter::class)]
#[ApiFilter(DealerFilter::class)]
#[ApiFilter(DateFilter::class, properties: [
    'publishedAt' => DateFilterInterface::EXCLUDE_NULL,
])]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
    'chamberOfCommerce' => 'partial',
])]
#[UniqueEntity(fields: ['chamberOfCommerce'])]
#[ORM\Index(fields: ['chamberOfCommerce'])]
#[ORM\Index(fields: ['name'])]
class Company
{
    public const SEARCH_OPERATION = 'search';
    public const CHAMBER_OF_COMMERCE_TO_ID_OPERATION = 'get-chamber-of-commerce-to-id';

    public const TYPE_TO_CLASS = [
        'company' => Company::class,
        'driving_school' => DrivingSchool::class,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ApiProperty(identifier: true)]
    public Ulid $id;

    #[Assert\Length(exactly: 8)]
    #[ORM\Column(unique: true, nullable: true)]
    public ?string $chamberOfCommerce = null;

    #[ORM\Column]
    public string $name;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeInterface $publishedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeInterface $disabledAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ApiProperty(readableLink: false, writableLink: false)]
    public ?User $disabledBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $disabledReason = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Address::class, cascade: ['all'], orphanRemoval: true)]
    #[ApiSubresource(maxDepth: 1)]
    #[Assert\Valid]
    public Collection $addresses;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: ContactMethod::class, cascade: ['all'], orphanRemoval: true)]
    #[ApiSubresource(maxDepth: 1)]
    #[Assert\Valid]
    public Collection $contactMethods;

    /** @var Collection<Employee>  */
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Employee::class, cascade: ['all'], orphanRemoval: true)]
    #[ApiSubresource(maxDepth: 1)]
    #[Assert\Valid]
    public Collection $employees;

    #[ORM\ManyToMany(targetEntity: Dealer::class, inversedBy: 'companies', cascade: ['persist'])]
    #[ApiSubresource(maxDepth: 1)]
    public Collection $dealers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: PaymentMethod::class, cascade: ['all'], orphanRemoval: true)]
    #[ApiSubresource(maxDepth: 1)]
    public Collection $paymentMethods;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Remark::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'desc'])]
    #[ApiSubresource(maxDepth: 1)]
    public Collection $remarks;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    public ?Company $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Company::class)]
    public Collection $children;

    public int $weight = 0;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->createdAt
            = $this->updatedAt
            = new DateTimeImmutable();

        $this->addresses = new ArrayCollection();
        $this->contactMethods = new ArrayCollection();
        $this->paymentMethods = new ArrayCollection();
        $this->dealers = new ArrayCollection();
        $this->employees = new ArrayCollection();
        $this->remarks = new ArrayCollection();

        $this->children = new ArrayCollection();
    }

    public function getType(): string
    {
        return array_flip(self::TYPE_TO_CLASS)[static::class];
    }

    public function publish(): void
    {
        $this->publishedAt = new DateTimeImmutable();
    }

    public function unPublish(): void
    {
        $this->publishedAt = null;
    }

    public function isPublished(): bool
    {
        return $this->publishedAt !== null
            && new DateTimeImmutable() >= $this->publishedAt;
    }

    public function enable(): void
    {
        $this->disabledAt = null;
        $this->disabledBy = null;
        $this->disabledReason = null;
    }

    public function disable(
        string $reason,
        User $disabledBy
    ): void {
        $this->disabledAt = new DateTimeImmutable();
        $this->disabledBy = $disabledBy;
        $this->disabledReason = $reason;
    }

    public function isDisabled(): bool
    {
        return $this->disabledAt !== null
            && new DateTimeImmutable() >= $this->disabledAt;
    }

    public function setDisabledBy(?User $user): void
    {
        $this->disabledBy = $user;
    }

    public function addAddress(Address $address): void
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setCompany($this);
        }
    }

    public function removeAddress(Address $address): void
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            $address->setCompany(null);
        }
    }

    public function addContactMethod(ContactMethod $contactMethod): void
    {
        if (!$this->contactMethods->contains($contactMethod)) {
            $this->contactMethods->add($contactMethod);
            $contactMethod->setCompany($this);
        }
    }

    public function removeContactMethod(ContactMethod $contactMethod): void
    {
        if ($this->contactMethods->contains($contactMethod)) {
            $this->contactMethods->removeElement($contactMethod);
            $contactMethod->setCompany(null);
        }
    }

    public function addPaymentMethod(PaymentMethod $paymentMethod): void
    {
        if (!$this->paymentMethods->contains($paymentMethod)) {
            $this->paymentMethods->add($paymentMethod);
            $paymentMethod->setCompany($this);
        }
    }

    public function removePaymentMethod(PaymentMethod $paymentMethod): void
    {
        $this->paymentMethods->removeElement($paymentMethod);
    }

    public function addDealer(Dealer $dealer): void
    {
        if (!$this->dealers->contains($dealer)) {
            $this->dealers->add($dealer);
            $dealer->addCompany($this);
        }
    }

    public function removeDealer(Dealer $dealer): void
    {
        if ($this->dealers->contains($dealer)) {
            $this->dealers->removeElement($dealer);
            $dealer->removeCompany($this);
        }
    }

    public function addEmployee(Employee $employee): void
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->company = $this;
            $employee->personal?->addCompany($employee);
        }
    }

    public function removeEmployee(Employee $employee): void
    {
        if ($this->employees->contains($employee)) {
            $this->employees->removeElement($employee);
            $employee->personal?->removeCompany($employee);
        }
    }

    public function addRemark(Remark $remark): void
    {
        if (!$this->remarks->contains($remark)) {
            $this->remarks->add($remark);
            $remark->setCompany($this);
        }
    }

    public function removeRemark(Remark $remark): void
    {
        if ($this->remarks->contains($remark)) {
            $this->remarks->removeElement($remark);
            $remark->setCompany(null);
        }
    }

    public function setParent(?Company $parent): void
    {
        if ($this->parent === $parent) {
            return;
        }

        $this->parent?->removeChild($this);
        $parent?->addChild($this);
        $this->parent = $parent;
    }

    public function addChild(Company $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
    }

    public function removeChild(Company $child): void
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }
    }
}
