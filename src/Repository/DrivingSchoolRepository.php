<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Ulid;

class DrivingSchoolRepository extends LensServiceEntityRepository
{
    use CompanyRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DrivingSchool::class);
    }

    /**
     * List driving schools that are nearby the provided driving school.
     */
    public function nearby(Ulid|string $drivingSchoolId, int $limit = 10): array
    {
        try {
            /** @var DrivingSchool $drivingSchoolEntity */
            $drivingSchoolEntity = $this->createQueryBuilder('drivingSchool')
                ->andWhere('drivingSchool.id = :drivingSchool')
                ->setParameter('drivingSchool', $drivingSchoolId, 'ulid')

                ->andWhere('drivingSchool.publishedAt IS NOT NULL AND drivingSchool.publishedAt <= CURRENT_TIMESTAMP()')

                ->join('drivingSchool.addresses', 'address')
                ->addSelect('address')
                ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
                ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundHttpException(sprintf(
                'Provided driving school "%s" does not exist, has no default address or is missing its latitude and/or longitude values.',
                $drivingSchoolId,
            ));
        }

        $drivingSchoolAddress = $drivingSchoolEntity->operatingAddress() ?? $drivingSchoolEntity->defaultAddress();

        $qb = $this->createQueryBuilder('drivingSchool')
            ->andWhere('drivingSchool.id != :drivingSchool')
            ->setParameter('drivingSchool', $drivingSchoolId, 'ulid')

            ->andWhere('drivingSchool.publishedAt IS NOT NULL AND drivingSchool.publishedAt <= CURRENT_TIMESTAMP()')

            ->join('drivingSchool.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
            ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

            ->addSelect('ST_Distance_Sphere(
                POINT(:longitude, :latitude),
                POINT(address.longitude, address.latitude)
            ) AS distance')
            ->setParameter('latitude', $drivingSchoolAddress->latitude)
            ->setParameter('longitude', $drivingSchoolAddress->longitude)
            ->orderBy('distance')

            ->setMaxResults($limit);

        return array_map(static function ($result) {
            $result[0]->distance = (float)$result['distance'];

            return $result[0];
        }, $qb->getQuery()->getResult());
    }
}
