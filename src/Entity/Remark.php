<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\RemarkRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: RemarkRepository::class)]
class Remark
{
    /** @deprecated use RemarkLevel::Default enum (value) instead */
    public const string DEFAULT = RemarkLevel::Default->value;

    /** @deprecated use RemarkLevel::Info enum (value) instead */
    public const string INFO = RemarkLevel::Info->value;

    /** @deprecated use RemarkLevel::Question enum (value) instead */
    public const string QUESTION = RemarkLevel::Question->value;

    /** @deprecated use RemarkLevel::Important enum (value) instead */
    public const string IMPORTANT = RemarkLevel::Important->value;

    /** @deprecated use RemarkLevel::Warning enum (value) instead */
    public const string WARNING = RemarkLevel::Warning->value;

    /** @deprecated use RemarkLevel::Danger enum (value) instead */
    public const string DANGER = RemarkLevel::Danger->value;

    /** @deprecated see RemarkLevel enum and use cases() */
    public const array LEVELS = [
        RemarkLevel::Default->value => RemarkLevel::Default->value,
        RemarkLevel::Info->value => RemarkLevel::Info->value,
        RemarkLevel::Question->value => RemarkLevel::Question->value,
        RemarkLevel::Important->value => RemarkLevel::Important->value,
        RemarkLevel::Warning->value => RemarkLevel::Warning->value,
        RemarkLevel::Danger->value => RemarkLevel::Danger->value,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column]
    public string $level = RemarkLevel::Default->value;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?User $createdBy = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'text')]
    public string $remark;

    #[ORM\ManyToOne(targetEntity: Personal::class, inversedBy: 'remarks')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    public ?Personal $personal = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'remarks')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    public ?Company $company = null;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->createdAt
            = $this->updatedAt
            = new DateTimeImmutable();
    }

    public function setPersonal(?Personal $personal): void
    {
        if ($this->personal === $personal) {
            return;
        }

        $this->personal?->removeRemark($this);
        $this->personal = $personal;
        $personal?->addRemark($this);
    }

    public function setCompany(?Company $company): void
    {
        if ($this->company === $company) {
            return;
        }

        $this->company?->removeRemark($this);
        $this->company = $company;
        $company?->addRemark($this);
    }

    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }
}
