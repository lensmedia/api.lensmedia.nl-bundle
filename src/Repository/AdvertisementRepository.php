<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Personal\Advertisement;

class AdvertisementRepository extends LensServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advertisement::class);
    }
}
