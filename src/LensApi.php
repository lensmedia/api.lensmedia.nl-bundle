<?php

namespace Lens\Bundle\LensApiBundle;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Lens\Bundle\LensApiBundle\Repository;
use RuntimeException;

class LensApi
{
    private readonly EntityManagerInterface $lensApiEntityManager;

    public function __construct(
        ManagerRegistry $managerRegistry,
        public readonly Repository\AddressRepository $addresses,
        public readonly Repository\AdvertisementRepository $advertisements,
        public readonly Repository\CompanyRepository $companies,
        public readonly Repository\ContactMethodRepository $contactMethods,
        public readonly Repository\DealerRepository $dealers,
        public readonly Repository\DebitRepository $debits,
        public readonly Repository\DriversLicenceRepository $driversLicences,
        public readonly Repository\DrivingSchoolRepository $drivingSchools,
        public readonly Repository\EmployeeRepository $employees,
        public readonly Repository\PaymentMethodRepository $paymentMethods,
        public readonly Repository\PersonalRepository $personals,
        public readonly Repository\RemarkRepository $remarks,
        public readonly Repository\ResultRepository $results,
        public readonly Repository\UserRepository $users,
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
