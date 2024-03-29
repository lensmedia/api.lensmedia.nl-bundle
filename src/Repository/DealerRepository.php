<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\Dealer;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Ulid;

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
            ->getRepository(DrivingSchool::class)
            ->createQueryBuilder('driving_school')
            ->andWhere('driving_school.disabledAt IS NULL')
            ->andWhere('driving_school.publishedAt IS NOT NULL AND driving_school.publishedAt < CURRENT_TIMESTAMP()')

            ->leftJoin('driving_school.driversLicences', 'driversLicence')
            ->addSelect('driversLicence')

            ->join('driving_school.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
            ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

            ->leftJoin('driving_school.contactMethods', 'contactMethod')
            ->addSelect('contactMethod')

            ->join('driving_school.dealers', 'dealer')
            ->andWhere('dealer.id = :dealer')
            ->setParameter('dealer', $dealerId, 'ulid')

            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a list of the closest dealers next to the specified company.
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

                ->join('company.addresses', 'address')
                ->addSelect('address')
                ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
                ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundHttpException(sprintf(
                'Provided company "%s" does not exist, is not published, has no default address or is missing its latitude and/or longitude values.',
                $companyId,
            ));
        }

        $companyAddress = $company->operatingAddress() ?? $company->defaultAddress();

        $qb = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('company')
            ->andWhere('company.id != :company')
            ->setParameter('company', $companyId, 'ulid')
            ->andWhere('company.publishedAt IS NOT NULL AND company.publishedAt <= CURRENT_TIMESTAMP()')

            ->join('company.dealers', 'dealer')
            ->andWhere('dealer.id = :dealer')
            ->setParameter('dealer', $dealerId, 'ulid')

            ->join('company.addresses', 'address')
            ->addSelect('address')
            ->andWhere('address.type IN (:address_types) AND address.latitude IS NOT NULL AND address.longitude IS NOT NULL')
            ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

            ->addSelect('ST_Distance_Sphere(
                POINT(:longitude, :latitude),
                POINT(address.longitude, address.latitude)
            ) AS distance')
            ->setParameter('latitude', $companyAddress->latitude)
            ->setParameter('longitude', $companyAddress->longitude)
            ->orderBy('distance')
            ->setMaxResults($maxResults);

        return array_map(static function ($result) {
            $result[0]->distance = $result['distance'];

            return $result[0];
        }, $qb->getQuery()->getResult());
    }
}
