<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AddressRepository extends EntityRepository
{
    public function getAddressByCompanyKVKAndType(string $kvk, string $type)
    {
        return $this->createQueryBuilder('address')
            ->leftJoin('address.company', 'company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->andWhere('address.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAddressesByCompanyId(string $id)
    {
        return $this->createQueryBuilder('address')
            ->leftJoin('address.company', 'company')
            ->andWhere('company.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->getQuery()
            ->getResult();
    }
}
