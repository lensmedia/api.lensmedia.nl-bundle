<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\ContactMethodInterface;
use Doctrine\ORM\EntityRepository;

class PersonalRepository extends EntityRepository
{
    //TODO we need to check this if we are going to create multiple accounts from 1 company
    public function getPersonalByCompanyKVK(string $kvk)
    {
        return $this->createQueryBuilder('personal')
            ->leftJoin('personal.companies', 'employee')
            ->leftJoin('employee.company', 'company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPersonalByEmail(string $email)
    {
        return $this->createQueryBuilder('personal')
            ->leftJoin('personal.contactMethods', 'contactMethods')
            ->andWhere('contactMethods.value = :email')
            ->setParameter('email', $email)
            ->andWhere('contactMethods.method = :method')
            ->setParameter('method', ContactMethodInterface::EMAIL)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPersonalByCompanyId(string $id)
    {
        return $this->createQueryBuilder('personal')
            ->leftJoin('personal.companies', 'employee')
            ->leftJoin('employee.company', 'company')
            ->andWhere('company.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
