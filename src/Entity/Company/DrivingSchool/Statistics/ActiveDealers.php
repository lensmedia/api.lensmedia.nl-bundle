<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\Statistics;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Lens\Bundle\LensApiBundle\Repository\ActiveDealersRepository;

#[ORM\Entity(repositoryClass: ActiveDealersRepository::class)]
class ActiveDealers
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column(type: 'date_immutable', options: ['default' => 'CURRENT_DATE'])]
    public DateTimeInterface $createdAt;

    public function __construct(
        #[ORM\Column]
        public string $name,

        #[ORM\Column(type: 'integer')]
        public int $total = 0,
    ) {
        $this->id = new Ulid();
        $this->createdAt = new DateTimeImmutable();
    }
}
