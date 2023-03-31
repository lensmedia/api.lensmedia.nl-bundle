<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class DriversLicenceRepository extends EntityRepository
{
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
