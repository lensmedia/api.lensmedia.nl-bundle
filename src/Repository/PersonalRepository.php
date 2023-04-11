<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\ContactMethod;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;

/**
 * @todo we need to check this if we are going to create multiple accounts from 1 company
 */
class PersonalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personal::class);
    }

    public function findOneByEmail(string $email): ?Personal
    {
        return $this->createQueryBuilder('personal')
            ->join('personal.contactMethods', 'contactMethod')
            ->andWhere('contactMethod.method = :method')
            ->setParameter('method', ContactMethod::EMAIL)

            ->andWhere('contactMethod.value = :email')
            ->setParameter('email', $email)

            ->getQuery()
            ->getOneOrNullResult();
    }
}
