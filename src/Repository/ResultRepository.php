<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\Result;
use Doctrine\ORM\EntityRepository;

class ResultRepository extends EntityRepository
{
    public function byCbrAndCategoryAndDate(
        Result $result
    ): ?Result {
        return $this->createQueryBuilder('result')
            ->andWhere('result.cbr = :cbr')
            ->setParameter('cbr', $result->cbr)
            ->andWhere('result.categoryCode = :category')
            ->setParameter('category', $result->categoryCode)
            ->andWhere('DATE(result.examPeriodStartedAt) = :date')
            ->setParameter('date', $result->examPeriodStartedAt)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
