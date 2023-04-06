<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DriversLicence;

class DriversLicenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DriversLicence::class);
    }

    public function getDriversLicencesByCompanyKVK(string $kvk)
    {
        return $this->createQueryBuilder('driversLicence')
            ->leftJoin('driversLicence.drivingSchools', 'drivingSchool')
            ->andWhere('drivingSchool.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->getQuery()
            ->getResult();
    }

    public function getDriversLicenceByCompanyKVKAndLabel(string $kvk, string $label)
    {
        return $this->createQueryBuilder('driversLicence')
            ->leftJoin('driversLicence.drivingSchools', 'drivingSchool')
            ->andWhere('drivingSchool.chamberOfCommerce = :kvk')
            ->setParameter('kvk', $kvk)
            ->andWhere('driversLicence.label = :label')
            ->setParameter('label', $label)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
