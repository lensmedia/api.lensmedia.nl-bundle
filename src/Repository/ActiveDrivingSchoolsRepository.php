<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ActiveDrivingSchoolsRepository extends EntityRepository
{
    public function findLatestEntry()
    {
        return $this->createQueryBuilder('activeDealers')
            ->orderBy('activeDealers.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
