<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\ContactMethodInterface;
use Doctrine\ORM\EntityRepository;

class ContactMethodRepository extends EntityRepository
{
    public function getContactMethodByCompanyKVK(string $kvk, string $method)
    {
        return $this->createQueryBuilder('contactMethod')
            ->leftJoin('contactMethod.company', 'company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->andWhere('contactMethod.method = :method')
            ->setParameter('method', $method)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPhoneMethodByCompanyKVK(string $kvk, string $label)
    {
        return $this->createQueryBuilder('contactMethod')
            ->leftJoin('contactMethod.company', 'company')
            ->andWhere('company.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->andWhere('contactMethod.label = :label')
            ->setParameter('label', $label)
            ->andWhere('contactMethod.method = :phone')
            ->setParameter('phone', ContactMethodInterface::PHONE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPersonalByEmail(string $email)
    {
        return $this->createQueryBuilder('contactMethod')
            ->andWhere('contactMethod.value = :email')
            ->andWhere('contactMethod.personal IS NOT NULL')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getContactMethodByPersonalId(string $id, string $method)
    {
        return $this->createQueryBuilder('contactMethod')
            ->leftJoin('contactMethod.personal', 'personal')
            ->andWhere('personal.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->andWhere('contactMethod.method = :method')
            ->setParameter('method', $method)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPhoneMethodByPersonalId(string $id, string $label)
    {
        return $this->createQueryBuilder('contactMethod')
            ->leftJoin('contactMethod.personal', 'personal')
            ->andWhere('personal.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->andWhere('contactMethod.method = :phone')
            ->setParameter('phone', ContactMethodInterface::PHONE)
            ->andWhere('contactMethod.label = :label')
            ->setParameter('label', $label)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getContactMethodByCompanyId(string $id, string $method)
    {
        return $this->createQueryBuilder('contactMethod')
            ->leftJoin('contactMethod.company', 'company')
            ->andWhere('company.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->andWhere('contactMethod.method = :method')
            ->setParameter('method', $method)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPhoneMethodByCompanyId(string $id, string $label)
    {
        return $this->createQueryBuilder('contactMethod')
            ->leftJoin('contactMethod.company', 'company')
            ->andWhere('company.id = :id')
            ->setParameter('id', $id, 'ulid')
            ->andWhere('contactMethod.method = :phone')
            ->setParameter('phone', ContactMethodInterface::PHONE)
            ->andWhere('contactMethod.label = :label')
            ->setParameter('label', $label)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
