<?php

namespace Lens\Bundle\LensApiBundle;

use Doctrine\ORM\EntityManagerInterface;
use Lens\Bundle\LensApiBundle\Repository;

class LensApi
{
    public function __construct(
        private readonly EntityManagerInterface $lensApiEntityManager,
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
