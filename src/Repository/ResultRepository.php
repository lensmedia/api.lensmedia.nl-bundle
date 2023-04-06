<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\Result;

class ResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Result::class);
    }

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
