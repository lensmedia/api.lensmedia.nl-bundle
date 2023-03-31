<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\AddressInterface;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Lens\Bundle\LensApiBundle\Entity\Personal\Advertisement;
use Doctrine\ORM\EntityRepository;

class DrivingSchoolRepository extends EntityRepository
{
    public function findWithAddressByCbrId(string $cbr): ?DrivingSchool
    {
        return $this->createQueryBuilder('drivingSchool')
            ->leftJoin('drivingSchool.addresses', 'address')
            ->andWhere('drivingSchool.cbr = :cbr')
            ->setParameter('cbr', $cbr)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByZipcodeAndStreetNumber(
        string $zipCode,
        int $streetNumber
    ): array {
        return $this->createQueryBuilder('drivingSchool')
            ->leftJoin('drivingSchool.addresses', 'address')
            ->andWhere('address.zipCode = :zipCode')
            ->setParameter('zipCode', $zipCode)
            ->andWhere('address.streetNumber = :streetNumber')
            ->setParameter('streetNumber', $streetNumber)
            ->andWhere('drivingSchool.cbr IS NULL')
            ->getQuery()
            ->getResult();
    }

    public function countActiveDrivingSchools(): int
    {
        return $this->createQueryBuilder('drivingSchool')
            ->select('count(drivingSchool.id)')
            ->andWhere('drivingSchool.disabledAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDrivingSchoolsWithDealer(string $dealer): int
    {
        return $this->createQueryBuilder('drivingSchool')
            ->select('count(drivingSchool.id)')
            ->leftJoin('drivingSchool.dealers', 'dealer')
            ->andWhere('dealer.name = :dealer')
            ->setParameter('dealer', $dealer)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countDrivingSchoolsCustomers(): int
    {
        return $this->createQueryBuilder('drivingSchool')
            ->select('count(DISTINCT drivingSchool.id)')
            ->innerJoin('drivingSchool.dealers', 'dealer')
            ->andWhere('dealer.id IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function mailingAddressesForAdvertising(): array
    {
        return $this->createQueryBuilder('drivingSchool')
            ->andWhere('drivingSchool.disabledAt IS NULL')

            ->join('drivingSchool.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type = :address_type')
            ->setParameter('address_type', AddressInterface::DEFAULT)

            ->join('drivingSchool.employees', 'employee')
            ->addSelect('employee')
            ->join('employee.personal', 'personal')
            ->addSelect('personal')
            ->join('personal.advertisements', 'advertisement')
            ->andWhere('advertisement.type = :mail')
            ->setParameter('mail', Advertisement::MAIL)

            ->getQuery()
            ->getResult();
    }

    public function withAddress(): array
    {
        return $this->createQueryBuilder('drivingSchool')
            ->leftJoin('drivingSchool.addresses', 'address')
            ->andWhere('drivingSchool.disabledAt IS NULL')
            ->orderBy('drivingSchool.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
