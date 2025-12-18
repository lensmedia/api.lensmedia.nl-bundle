<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\AddressType;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\Dealer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Ulid;

use function sprintf;

class DealerRepository extends LensServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dealer::class);
    }

    /**
     * Returns a list of driving schools for a given dealer optimized for the leaflet map displays.
     */
    public function map(Ulid|string $dealerId): array
    {
        // Approaching from other side, its easier due to joins.
        return $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('company')

            ->andWhere('company.disabledAt IS NULL')
            ->andWhere('company.publishedAt IS NOT NULL AND company.publishedAt < CURRENT_TIMESTAMP()')

            ->join('company.drivingSchool', 'drivingSchool')
            ->addSelect('drivingSchool')

            ->leftJoin('drivingSchool.driversLicences', 'driversLicence')
            ->addSelect('driversLicence')

            ->join('company.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
            ->setParameter('address_types', [AddressType::Operating->value, AddressType::Default->value])

            ->leftJoin('company.contactMethods', 'contactMethod')
            ->addSelect('contactMethod')

            ->join('company.dealers', 'dealer')
            ->andWhere('dealer.id = :dealer')
            ->setParameter('dealer', $dealerId, 'ulid')

            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a list of the closest dealers next to the specified company.
     *
     * @return Company[]
     */
    public function nearby(Ulid|string $dealerId, Ulid|string $companyId, int $maxResults = 10): array
    {
        // Check if we have provided a company which can be used.
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
                ->setParameter('address_types', [AddressType::Operating->value, AddressType::Default->value])

                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundHttpException(sprintf(
                'Provided company "%s" does not exist, is not published, has no default address or is missing its latitude and/or longitude values.',
                $companyId,
            ));
        }

        $originCoords = $company->operatingCoords();
        if (null === $originCoords) {
            return [];
        }

        $qb = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('company')
            ->andWhere('company.id != :company')
            ->setParameter('company', $companyId, 'ulid')
            ->andWhere('company.publishedAt IS NOT NULL AND company.publishedAt <= CURRENT_TIMESTAMP()')

            ->join('company.drivingSchool', 'drivingSchool')
            ->addSelect('drivingSchool')

            ->join('company.dealers', 'dealer')
            ->andWhere('dealer.id = :dealer')
            ->setParameter('dealer', $dealerId, 'ulid')

            ->join('company.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
            ->setParameter('address_types', [AddressType::Operating->value, AddressType::Default->value])

            ->addSelect('ST_Distance_Sphere(
                POINT(:longitude, :latitude),
                POINT(address.longitude, address.latitude)
            ) AS distance')
            ->setParameter('latitude', $originCoords[0])
            ->setParameter('longitude', $originCoords[1])
            ->orderBy('distance')
            ->setMaxResults($maxResults);

        return array_map(static function ($result) {
            $result[0]->distance = $result['distance'];

            return $result[0];
        }, $qb->getQuery()->getResult());
    }
}
