<?php

namespace Lens\Bundle\LensApiBundle\Entity\Personal;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\AdvertisementRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;

/**
 * Currently 3 types of advertisement are possible: mail, email, phone
 * could be further specified at some point with email_promotions,
 * email_news whatever.
 *
 * Only the owner of a company can opt in/out for mail.
 * Email is always personal, we don't send advertisements directed
 * to companies, but always directed to a person.
 */
#[ApiFilter(SearchFilter::class, properties: [
    'type' => 'partial',
])]
#[ORM\Entity(repositoryClass: AdvertisementRepository::class)]
#[UniqueEntity(fields: 'type', message: 'advertisement.type.unique_entity')]
#[ApiResource(
    denormalizationContext: [
        'groups' => ['advertisement']
    ],
    normalizationContext: [
        'groups' => ['advertisement'],
    ]
)]
class Advertisement
{
    public const PHONE = 'phone';
    public const EMAIL = 'email';
    public const MAIL = 'mail';

    public const TYPES = [
        self::PHONE => self::PHONE,
        self::EMAIL => self::EMAIL,
        self::MAIL => self::MAIL,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column(unique: true)]
    public string $type;

    #[ORM\ManyToMany(targetEntity: Personal::class, mappedBy: 'advertisements')]
    public Collection $personals;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->personals = new ArrayCollection();
    }

    public function addPersonal(Personal $personal): void
    {
        if (!$this->personals->contains($personal)) {
            $this->personals->add($personal);
            $personal->addAdvertisement($this);
        }
    }

    public function removePersonal(Personal $personal): void
    {
        if ($this->personals->contains($personal)) {
            $this->personals->removeElement($personal);
            $personal->removeAdvertisement($this);
        }
    }
}
