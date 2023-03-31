<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Company\Company;
use App\Entity\Personal\Personal;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\RemarkInterface;
use Lens\Bundle\LensApiBundle\Repository\RemarkRepository;
use Symfony\Component\Uid\Ulid;

#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: ['get', 'patch', 'delete'],
    subresourceOperations: [
        'api_companies_remarks_get_subresource' => [
            'normalization_context' => [
                'groups' => ['company'],
            ],
        ],
        'api_driving_schools_remarks_get_subresource' => [
            'normalization_context' => [
                'groups' => ['driving_school'],
            ],
        ],
        'api_personals_remarks_get_subresource' => [
            'normalization_context' => [
                'groups' => ['personal'],
            ],
        ],
    ],
    denormalizationContext: [
        'groups' => ['remark'],
    ],
    normalizationContext: [
        'groups' => ['remark'],
    ],
)]
#[ORM\Entity(repositoryClass: RemarkRepository::class)]
class Remark
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column]
    public string $level = RemarkInterface::DEFAULT;

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
        $personal?->addRemark($this);
        $this->personal = $personal;
    }

    public function setCompany(?Company $company): void
    {
        if ($this->company === $company) {
            return;
        }

        $this->company?->removeRemark($this);
        $company?->addRemark($this);
        $this->company = $company;
    }

    public function setCreatedBy(?User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }
}
