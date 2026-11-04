<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Ulid;

use function sprintf;

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
    public function nearby(Company|Ulid|string $company, int $limit = 10): array
    {
        if ($company instanceof Company) {
            $company = $company->id;
        }

        try {
            /** @var Company $companyResult */
            $companyResult = $this->getEntityManager()
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
                ->setParameter('address_types', [Address::OPERATING, Address::DEFAULT])

                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException) {
            throw new NotFoundHttpException(sprintf(
                'Provided driving school "%s" does not exist, has no default address or is missing its latitude and/or longitude values.',
                $company,
            ));
        }

        $originCoords = $companyResult->operatingCoords();
        if (null === $originCoords) {
            throw new RuntimeException('Company has no coords on operating or default address.');
        }

        $results = $this->getEntityManager()
            ->getRepository(Company::class)
            ->createQueryBuilder('company')
            ->andWhere('company.id != :company')
            ->setParameter('company', $company, 'ulid')

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

    /**
     * Driving schools with employees whose drivers licences were not sent to Brevo since $syncedBefore.
     *
     * Disabled driving schools are included, their employees lose the drivers licences in Brevo.
     *
     * @return DrivingSchool[] never sent first (MySQL sorts NULL first), then longest ago
     */
    public function dueForBrevo(DateTimeImmutable $syncedBefore, int $limit): array
    {
        return $this->createQueryBuilder('drivingSchool')
            ->join('drivingSchool.company', 'company')
            ->addSelect('company')
            ->andWhere(sprintf('EXISTS (SELECT employee.id FROM %s employee WHERE employee.company = company)', Employee::class))
            ->andWhere('drivingSchool.brevoSyncedAt IS NULL OR drivingSchool.brevoSyncedAt < :syncedBefore')
            ->setParameter('syncedBefore', $syncedBefore, Types::DATETIME_IMMUTABLE)
            ->orderBy('drivingSchool.brevoSyncedAt')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param DrivingSchool[] $drivingSchools
     */
    public function markBrevoSynced(array $drivingSchools): void
    {
        $this->updateBrevoSyncedAt($drivingSchools, new DateTimeImmutable());
    }

    /**
     * Puts the driving schools at the front of the Brevo queue.
     *
     * @param DrivingSchool[] $drivingSchools
     */
    public function markBrevoDue(array $drivingSchools): void
    {
        $this->updateBrevoSyncedAt($drivingSchools, null);
    }

    /**
     * A DQL update skips the entity listeners, a flush would reindex every driving school in Meilisearch.
     * The loaded entities keep their old value, which a later flush leaves alone as it is unchanged.
     *
     * @param DrivingSchool[] $drivingSchools
     */
    private function updateBrevoSyncedAt(array $drivingSchools, ?DateTimeImmutable $brevoSyncedAt): void
    {
        if (!$drivingSchools) {
            return;
        }

        $this->getEntityManager()
            ->createQuery(sprintf(
                'UPDATE %s drivingSchool SET drivingSchool.brevoSyncedAt = :brevoSyncedAt WHERE drivingSchool.company IN (:companies)',
                DrivingSchool::class,
            ))
            ->setParameter('brevoSyncedAt', $brevoSyncedAt, Types::DATETIME_IMMUTABLE)
            ->setParameter('companies', array_map(
                static fn (DrivingSchool $drivingSchool) => $drivingSchool->company->id->toBinary(),
                $drivingSchools,
            ), ArrayParameterType::BINARY)
            ->execute();
    }
}
