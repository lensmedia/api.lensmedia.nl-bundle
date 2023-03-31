<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RemarkRepository extends EntityRepository
{
    public function getRemarkByCompanyId(string $companyId)
    {
        return $this->createQueryBuilder('remark')
            ->leftJoin('remark.company', 'company')
            ->andWhere('company.id = :companyId')
            ->setParameter('companyId', $companyId, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
