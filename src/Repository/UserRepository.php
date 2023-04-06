<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\User;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function hasUserByCompanyChamberOfCommerce(string $chamberOfCommerce): bool
    {
        return $this->createQueryBuilder('user')
            ->select('count(user.id)')
            ->leftJoin('user.personal', 'personal')
            ->leftJoin('personal.companies', 'employee')
            ->leftJoin('employee.company', 'company')

            ->andWhere('company.chamberOfCommerce = :chamberOfCommerce')
            ->setParameter('chamberOfCommerce', $chamberOfCommerce)

            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function getUserByCompanyChamberOfCommerce(string $chamberOfCommerce): User
    {
        return $this->createQueryBuilder('user')
            ->leftJoin('user.personal', 'personal')
            ->addSelect('personal')
            ->leftJoin('personal.companies', 'employee')
            ->addSelect('employee')
            ->leftJoin('employee.company', 'company')
            ->addSelect('company')

            ->andWhere('company.chamberOfCommerce = :chamberOfCommerce')
            ->setParameter('chamberOfCommerce', $chamberOfCommerce)

            ->getQuery()
            ->getSingleResult();
    }
}
