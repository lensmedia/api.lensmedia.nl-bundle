<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\Address;

class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

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
