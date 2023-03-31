<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AdvertisementRepository extends EntityRepository
{
    public function getAdvertisementsByCompanyKVK(string $kvk)
    {
        return $this->createQueryBuilder('advertisement')
            ->leftJoin('advertisement.personals', 'personal')
            ->leftJoin('personal.companies', 'employee')
            ->leftJoin('employee.company', 'company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->getQuery()
            ->getResult();
    }

    public function getAdvertisementByCompanyKVKAndType(string $kvk, string $type)
    {
        return $this->createQueryBuilder('advertisement')
            ->leftJoin('advertisement.personals', 'personal')
            ->leftJoin('personal.companies', 'employee')
            ->leftJoin('employee.company', 'company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->andWhere('advertisement.type = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAdvertisementByEmail(string $email)
    {
        return $this->createQueryBuilder('advertisement')
            ->leftJoin('advertisement.personals', 'personal')
            ->leftJoin('personal.contactMethods', 'contactMethod')
            ->andWhere('contactMethod.value = :email')
            ->setParameter('email', $email)
            ->andWhere('advertisement.type = :type')
            ->setParameter('type', 'email')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
