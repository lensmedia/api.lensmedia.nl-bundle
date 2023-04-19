<?php

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
    public const DEFAULT = 'default';
    public const INFO = 'info';
    public const QUESTION = 'question';
    public const IMPORTANT = 'important';
    public const WARNING = 'warning';
    public const DANGER = 'danger';

    public const LEVELS = [
        self::DEFAULT => self::DEFAULT,
        self::INFO => self::INFO,
        self::QUESTION => self::QUESTION,
        self::IMPORTANT => self::IMPORTANT,
        self::WARNING => self::WARNING,
        self::DANGER => self::DANGER,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column]
    public string $level = self::DEFAULT;

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
