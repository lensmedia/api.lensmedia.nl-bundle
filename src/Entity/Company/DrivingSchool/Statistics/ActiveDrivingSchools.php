<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\Statistics;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
class ActiveDrivingSchools
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column(type: 'integer')]
    public int $total = 0;

    #[ORM\Column(type: 'integer')]
    public int $active;

    #[ORM\Column(type: 'datetime_immutable')]
    public DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->createdAt = new DateTimeImmutable();
    }
}
