<?php

namespace Lens\Bundle\LensApiBundle\Entity\Statistics;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\ActiveDealersRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ActiveDealersRepository::class)]
class ActiveDealers
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column(type: 'date_immutable')]
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
