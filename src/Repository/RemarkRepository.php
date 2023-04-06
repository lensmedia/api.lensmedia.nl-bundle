<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\Remark;

class RemarkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Remark::class);
    }

    public function getRemarkByCompanyId(string $companyId): ?Remark
    {
        return $this->createQueryBuilder('remark')
            ->leftJoin('remark.company', 'company')
            ->andWhere('company.id = :companyId')
            ->setParameter('companyId', $companyId, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
