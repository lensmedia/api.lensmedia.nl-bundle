<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Repository;

use DateTimeImmutable;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\AddressType;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\Dealer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Ulid;

class DealerRepository extends LensServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dealer::class);
    }

    /**
     * Marks a company as having sold something from a supplier (e.g. X purchased something of itheorie/theorieboek).
     *
     * Timestamp allows us to specify a timestamp of when the dealer was last active, this allows us to call updates
     * from various places (and times) while keeping the most recent timestamp.
     */
    public function mark(Company $company, Company $purchasedFrom, DateTimeImmutable $timestamp): Dealer
    {
        // Cache created entries to avoid multiple queries within the same request.
        // This also avoids unique (dealer/company) violations, if multiple calls are made for the same company+dealer,
        // then the timestamp will just be updated if needed.
        static $trackedEntries = [];

        $index = $this->index($company, $purchasedFrom);
        $result = $trackedEntries[$index] ?? null;

        $result ??= $this->createQueryBuilder('dealers')
            ->andWhere('dealers.dealer = :dealer')
            ->setParameter('dealer', $company->id, 'ulid')

            ->andWhere('dealers.supplier = :company')
            ->setParameter('company', $purchasedFrom->id, 'ulid')

            ->getQuery()
            ->getOneOrNullResult();

        $result ??= Dealer::mark($company, $purchasedFrom);

        $result->update($timestamp);

        $trackedEntries[$index] = $result;

        return $result;
    }

    /**
     * Get all dealers for a specific company (companies that purchased from dealerId).
     */
    public function map(Company|Ulid|string $supplier): array
    {
        if ($supplier instanceof Company) {
            $supplier = $supplier->id;
        }

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

            ->leftJoin('company.dealers', 'dealer')
            ->andWhere('dealer.supplier = :supplier')
            ->setParameter('supplier', $supplier, 'ulid')

            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a list of the closest dealers for supplier next to the specified company (for those with latitude/longitude).
     *
     * @return Company[]
     */
    public function nearby(Company|Ulid|string $company, Company|Ulid|string $supplier, int $maxResults = 10): array
    {
        if ($company instanceof Company) {
            $company = $company->id;
        }

        if ($supplier instanceof Company) {
            $supplier = $supplier->id;
        }

        // Check if we have provided a company which can be used.
        try {
            /** @var Company $companyResult */
            $companyResult = $this->manager()
                ->getRepository(Company::class)
                ->createQueryBuilder('company')
                ->andWhere('company.id = :company')
                ->setParameter('company', $company, 'ulid')
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
                $company,
            ));
        }

        $originCoords = $companyResult->operatingCoords();
        if (null === $originCoords) {
            return [];
        }

        $qb = $this->manager()
            ->getRepository(Company::class)
            ->createQueryBuilder('company')
            ->andWhere('company.id != :company')
            ->setParameter('company', $company, 'ulid')
            ->andWhere('company.publishedAt IS NOT NULL AND company.publishedAt <= CURRENT_TIMESTAMP()')

            ->join('company.drivingSchool', 'drivingSchool')
            ->addSelect('drivingSchool')

            ->join('company.suppliers', 'supplier')
            ->andWhere('supplier.id = :supplier')
            ->setParameter('supplier', $supplier, 'ulid')

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

    private function index(Company $dealer, Company $supplier): string
    {
        return sprintf('%s_%s', $dealer->id->toHex(), $supplier->id->toHex());
    }
}
