<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\Statistics\ActiveDrivingSchools;

class ActiveDrivingSchoolsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveDrivingSchools::class);
    }

    public function findLatestEntry()
    {
        return $this->createQueryBuilder('activeDealers')
            ->orderBy('activeDealers.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
