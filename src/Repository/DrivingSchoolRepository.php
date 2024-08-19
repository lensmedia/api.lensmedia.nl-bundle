<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Ulid;

class DrivingSchoolRepository extends LensServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DrivingSchool::class);
    }

    /**
     * List driving schools that are nearby the provided driving school.
     *
     * @return Company[]
     */
    public function nearby(Ulid|string $companyId, int $limit = 10): array
    {
        try {
            /** @var Company $company */
            $company = $this->getEntityManager()
                ->getRepository(Company::class)
                ->createQueryBuilder('company')
                ->andWhere('company.id = :company')
                ->setParameter('company', $companyId, 'ulid')
                ->andWhere('company.publishedAt IS NOT NULL AND company.publishedAt <= CURRENT_TIMESTAMP()')

                // Only select driving schools so far.
                ->join('company.drivingSchool', 'drivingSchool')
                ->addSelect('drivingSchool')

                ->join('company.addresses', 'address')
                ->addSelect('address')
                ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
                ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundHttpException(sprintf(
                'Provided driving school "%s" does not exist, has no default address or is missing its latitude and/or longitude values.',
                $companyId,
            ));
        }

        $originCoords = $company->operatingCoords();
        if (null === $originCoords) {
            throw new RuntimeException('Company has no coords on operating or default address.');
        }

        $results = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('company')
            ->andWhere('company.id != :company')
            ->setParameter('company', $companyId, 'ulid')

            ->andWhere('company.publishedAt IS NOT NULL AND company.publishedAt <= CURRENT_TIMESTAMP()')
            ->join('company.drivingSchool', 'drivingSchool')
            ->addSelect('drivingSchool')

            ->join('company.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
            ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

            ->addSelect('ST_Distance_Sphere(
                POINT(:longitude, :latitude),
                POINT(address.longitude, address.latitude)
            ) AS distance')
            ->setParameter('latitude', $originCoords[0])
            ->setParameter('longitude', $originCoords[1])
            ->orderBy('distance')

            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Remap select list back to company object.
        return array_map(static function ($result) {
            $result[0]->distance = (float)$result['distance'];

            return $result[0];
        }, $results);
    }
}
