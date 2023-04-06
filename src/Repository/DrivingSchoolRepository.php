<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Lens\Bundle\LensApiBundle\Entity\Personal\Advertisement;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Ulid;

class DrivingSchoolRepository extends ServiceEntityRepository
{
    use CompanyRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DrivingSchool::class);
    }

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
            ->setParameter('address_type', Address::DEFAULT)

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

    public function nearby(Ulid|string $drivingSchoolId): array
    {
        try {
            $drivingSchoolEntity = $this->createQueryBuilder('drivingSchool')
                ->andWhere('drivingSchool.id = :drivingSchool')
                ->setParameter('drivingSchool', $drivingSchoolId, 'ulid')

                ->andWhere('drivingSchool.publishedAt IS NOT NULL AND drivingSchool.publishedAt <= CURRENT_TIMESTAMP()')

                ->join('drivingSchool.addresses', 'address')
                ->addSelect('address')
                ->andWhere('address.type = :address_type AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
                ->setParameter('address_type', Address::DEFAULT)

                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundHttpException(sprintf(
                'Provided drivingSchool "%s" does not exist, has no default address or is missing its latitude and/or longitude values.',
                $drivingSchoolId,
            ));
        }

        $drivingSchoolAddress = $drivingSchoolEntity->addresses[0];

        $qb = $this->createQueryBuilder('drivingSchool')
            ->andWhere('drivingSchool.id != :drivingSchool')
            ->setParameter('drivingSchool', $drivingSchoolId, 'ulid')

            ->andWhere('drivingSchool.publishedAt IS NOT NULL AND drivingSchool.publishedAt <= CURRENT_TIMESTAMP()')

            ->join('drivingSchool.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type = :address_type AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
            ->setParameter('address_type', Address::DEFAULT)

            ->addSelect('ST_Distance_Sphere(
                POINT(:longitude, :latitude),
                POINT(address.longitude, address.latitude)
            ) AS distance')
            ->setParameter('latitude', $drivingSchoolAddress->latitude)
            ->setParameter('longitude', $drivingSchoolAddress->longitude)
            ->orderBy('distance')

            ->setMaxResults(10);

        return array_map(static function ($result) {
            $result[0]->distance = (float)$result['distance'];

            return $result[0];
        }, $qb->getQuery()->getResult());
    }

    public function forSitemap(): array
    {
        return $this->createQueryBuilder('drivingSchool', 'drivingSchool.chamberOfCommerce')
            ->select('drivingSchool.id', 'drivingSchool.chamberOfCommerce', 'drivingSchool.name')
            ->andWhere('drivingSchool.disabledAt IS NULL')
            ->getQuery()
            ->getResult();
    }
}
