<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\Result;

class ResultRepository extends LensServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Result::class);
    }
}
