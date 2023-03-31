<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DealerRepository extends EntityRepository
{
    public function getDealersByCompanyKVK(string $kvk)
    {
        return $this->createQueryBuilder('dealer')
            ->leftJoin('dealer.companies','company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->getQuery()
            ->getResult();
    }

    public function getDealerByCompanyKVKAndName(string $kvk, string $name){
        return $this->createQueryBuilder('dealer')
            ->leftJoin('dealer.companies','company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->andWhere('dealer.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
