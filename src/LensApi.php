<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use RuntimeException;

/**
 * @property Repository\AddressRepository $addresses
 * @property Repository\AdvertisementRepository $advertisements
 * @property Repository\CompanyRepository $companies
 * @property Repository\ContactMethodRepository $contactMethods
 * @property Repository\DealerRepository $dealers
 * @property Repository\DebitRepository $debits
 * @property Repository\DriversLicenceRepository $driversLicences
 * @property Repository\DrivingSchoolRepository $drivingSchools
 * @property Repository\EmployeeRepository $employees
 * @property Repository\PaymentMethodRepository $paymentMethods
 * @property Repository\PersonalRepository $personals
 * @property Repository\RemarkRepository $remarks
 * @property Repository\ResultRepository $results
 * @property Repository\UserRepository $users
 */
class LensApi
{
    private readonly EntityManagerInterface $lensApiEntityManager;

    public function __construct(
        ManagerRegistry $managerRegistry,
        public readonly ServiceEntityRepositoryInterface $addresses,
        public readonly ServiceEntityRepositoryInterface $advertisements,
        public readonly ServiceEntityRepositoryInterface $companies,
        public readonly ServiceEntityRepositoryInterface $contactMethods,
        public readonly ServiceEntityRepositoryInterface $dealers,
        public readonly ServiceEntityRepositoryInterface $debits,
        public readonly ServiceEntityRepositoryInterface $driversLicences,
        public readonly ServiceEntityRepositoryInterface $drivingSchools,
        public readonly ServiceEntityRepositoryInterface $employees,
        public readonly ServiceEntityRepositoryInterface $paymentMethods,
        public readonly ServiceEntityRepositoryInterface $personals,
        public readonly ServiceEntityRepositoryInterface $remarks,
        public readonly ServiceEntityRepositoryInterface $results,
        public readonly ServiceEntityRepositoryInterface $users,
    ) {
        try {
            $this->lensApiEntityManager = $managerRegistry->getManager('lens_api');
        } catch (InvalidArgumentException) {
            throw new RuntimeException('No entity manager named "lens_api" found. Did you forget to add the "lens_lens_api.yaml" config?');
        }
    }

    public function manager(): EntityManagerInterface
    {
        return $this->lensApiEntityManager;
    }

    public function persist(object $object): void
    {
        $this->manager()->persist($object);
    }

    public function remove(object $object): void
    {
        $this->manager()->remove($object);
    }

    public function clear(): void
    {
        $this->manager()->clear();
    }

    /**
     * @param class-string $entityName
     */
    public function reference(string $entityName, mixed $id): object
    {
        return $this->manager()->getReference($entityName, $id);
    }

    public function detach(object $object): void
    {
        $this->manager()->detach($object);
    }

    public function refresh(object $object): void
    {
        $this->manager()->refresh($object);
    }

    public function flush(): void
    {
        $this->manager()->flush();
    }
}
