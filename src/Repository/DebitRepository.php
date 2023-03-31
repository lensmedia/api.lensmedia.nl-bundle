<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DebitRepository extends EntityRepository
{
    public function getDebitWithCompanyKVK(string $kvk){
        return $this->createQueryBuilder('debit')
            ->leftJoin('debit.company', 'company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
