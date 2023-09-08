<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\AddressTrait;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Lens\Bundle\LensApiBundle\Entity\ContactMethod;
use Lens\Bundle\LensApiBundle\Entity\ContactMethodTrait;
use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\PaymentMethod;
use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\PaymentMethodTrait;
use Lens\Bundle\LensApiBundle\Entity\Remark;
use Lens\Bundle\LensApiBundle\Entity\User;
use Lens\Bundle\LensApiBundle\Repository\CompanyRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type')]
#[ORM\DiscriminatorMap(self::TYPE_TO_CLASS)]
#[ORM\Index(fields: ['chamberOfCommerce'])]
#[ORM\Index(fields: ['name'])]
#[ORM\Index(fields: ['affiliate'])]
#[UniqueEntity(fields: ['chamberOfCommerce'], message: 'company.chamber_of_commerce.unique_entity')]
class Company
{
    use AddressTrait;
    use ContactMethodTrait;
    use EmployeeTrait;
    use PaymentMethodTrait;

    public const COMPANY = 'company';
    public const DRIVING_SCHOOL = 'driving_school';

    private const TYPE_TO_CLASS = [
        self::COMPANY => self::class,
        self::DRIVING_SCHOOL => DrivingSchool::class,
    ];

    public const TYPES = [
        self::COMPANY => self::COMPANY,
        self::DRIVING_SCHOOL => self::DRIVING_SCHOOL,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[Assert\Length(exactly: 8)]
    #[ORM\Column(unique: true, nullable: true)]
    public ?string $chamberOfCommerce = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    public string $name;

    /**
     * @var int 0-65535 **WARNING** Using `#[ORM\GeneratedValue(strategy: 'AUTO')]`
     *          does not work and breaks the ID column when, at least persisting
     *          new entities KEEP THE COLUMN DEFINITION AND SUFFER THROUGH THE MIGRATIONS
     */
    #[Assert\Range(min: 0, max: 65535)]
    #[ORM\Column(type: 'smallint', columnDefinition: 'SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE')]
    public int $affiliate;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeImmutable $disabledAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    public ?User $disabledBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $disabledReason = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Address::class, cascade: ['all'], orphanRemoval: true)]
    #[Assert\Valid]
    public Collection $addresses;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: ContactMethod::class, cascade: ['all'], orphanRemoval: true)]
    #[Assert\Valid]
    public Collection $contactMethods;

    /** @var Collection<Employee> */
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Employee::class, cascade: ['all'], orphanRemoval: true)]
    #[Assert\Valid]
    public Collection $employees;

    #[ORM\ManyToMany(targetEntity: Dealer::class, mappedBy: 'companies', cascade: ['persist'])]
    public Collection $dealers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: PaymentMethod::class, cascade: ['all'], orphanRemoval: true)]
    public Collection $paymentMethods;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Remark::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'desc'])]
    public Collection $remarks;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    public ?Company $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
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

    public function publish(): void
    {
        $this->publishedAt = new DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->publishedAt = null;
    }

    public function isPublished(): bool
    {
        return null !== $this->publishedAt && new DateTimeImmutable() >= $this->publishedAt;
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
        return null !== $this->disabledAt
            && new DateTimeImmutable() >= $this->disabledAt;
    }

    public function isDrivingSchool(): bool
    {
        return $this instanceof DrivingSchool;
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
        }
    }

    public function removeEmployee(Employee $employee): void
    {
        if ($this->employees->contains($employee)) {
            $this->employees->removeElement($employee);
            $employee->personal->removeCompany($employee);
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

    public function setParent(?self $parent): void
    {
        if ($this->parent === $parent) {
            return;
        }

        $this->parent?->removeChild($this);
        $this->parent = $parent;
        $parent?->addChild($this);
    }

    public function addChild(self $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
    }

    public function removeChild(self $child): void
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            $child->setParent(null);
        }
    }

    public function isDealer(string $name): bool
    {
        return $this->dealers->exists(
            static fn (int $index, Dealer $dealer) => mb_strtolower($name) === $dealer->name,
        );
    }
}
