<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function getUserByCompanyChamberOfCommerce(string $chamberOfCommerce): ?User
    {
        return $this->createQueryBuilder('user')
            ->leftJoin('user.personal', 'personal')
            ->leftJoin('personal.companies', 'employee')
            ->leftJoin('employee.company', 'company')
            ->andWhere('company.chamberOfCommerce = :chamberOfCommerce')
            ->setParameter('chamberOfCommerce', $chamberOfCommerce)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
