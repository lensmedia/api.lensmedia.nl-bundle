<?php

namespace Lens\Bundle\LensApiBundle\Entity\Statistics;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\ActiveDrivingSchoolsRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ActiveDrivingSchoolsRepository::class)]
class ActiveDrivingSchools
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column(type: 'date_immutable')]
    public DateTimeInterface $createdAt;

    public function __construct(
        #[ORM\Column(type: 'integer')]
        public int $total = 0,
        #[ORM\Column(type: 'integer')]
        public int $active = 0,
    ) {
        $this->id = new Ulid();

        $this->createdAt = new DateTimeImmutable();
    }
}
