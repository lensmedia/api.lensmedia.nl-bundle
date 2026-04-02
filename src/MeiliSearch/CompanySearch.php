<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\MeiliSearch;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DriversLicence;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DrivingSchool;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;
use Lens\Bundle\LensApiBundle\Entity\ContactMethod;
use Lens\Bundle\LensApiBundle\Entity\ContactMethodMethod;
use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\Debit;
use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\PaymentMethod;
use Lens\Bundle\MeiliSearchBundle\Attribute\Index;
use Lens\Bundle\MeiliSearchBundle\Document;
use Lens\Bundle\MeiliSearchBundle\Exception\InvalidTransformData;
use LogicException;

#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Company::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: DrivingSchool::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: DrivingSchool::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: DrivingSchool::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Address::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Address::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Address::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: ContactMethodMethod::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: ContactMethodMethod::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: ContactMethodMethod::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: PaymentMethod::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: PaymentMethod::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: PaymentMethod::class)]
#[AsEntityListener(event: Events::postPersist, method: 'onUpdate', entityManager: 'lens_api', entity: Employee::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'onUpdate', entityManager: 'lens_api', entity: Employee::class)]
#[AsEntityListener(event: Events::postRemove, method: 'onRemove', entityManager: 'lens_api', entity: Employee::class)]
readonly class CompanySearch extends Search
{
    use MapAddressesTrait;
    use MapContactMethodsTrait;
    use MapPersonalTrait;

    public const string INDEX = 'company';

    public function supports(): array
    {
        return [Company::class];
    }

    public function getIndexes(): array
    {
        return [
            new Index(uid: self::INDEX, settings: [
                'filterableAttributes' => [
                    'type',
                    'chamberOfCommerce',
                    'affiliate',
                    'cbr',
                    'licenses',
                    'createdAt',
                    'updatedAt',
                    'publishedAt',
                    'disabledAt',
                    '_geo',
                ],
            ], client: 'lens_api'),
        ];
    }

    public function onUpdate(object $object, LifecycleEventArgs $event): void
    {
        if ($this->isLoadingFixturesInDebug()) {
            return;
        }

        if ($company = $this->companyFromParameter($object)) {
            $this->lensMeiliSearch->addDocuments(self::INDEX, [$company]);
        }
    }

    public function onRemove(object $object, LifecycleEventArgs $event): void
    {
        if ($company = $this->companyFromParameter($object)) {
            $this->lensMeiliSearch->index(self::INDEX)->deleteDocument((string)$company->id);
        }
    }

    public function toDocument(object $data, array $context = []): Document
    {
        if (!($data instanceof Company)) {
            throw new InvalidTransformData($data, Company::class);
        }

        $document = [
            'id' => $data->id,
            'type' => $data->isDrivingSchool() ? 'driving_school' : 'company',
            'name' => $data->name,
            'chamberOfCommerce' => $data->chamberOfCommerce,
            'affiliate' => $data->affiliate ?? 0,
            'customerNumber' => $data->customerNumber(),

            'addresses' => $this->mapAddresses($data->addresses),
            'contact' => $this->mapContactMethods($data->contactMethods),

            'payment' => $this->mapPaymentMethods($data->paymentMethods),
            'employees' => $this->mapEmployees($data->employees),

            'cbr' => $data->drivingSchool?->cbr,
            'licenses' => $data->drivingSchool?->driversLicences->map(
                static fn (DriversLicence $licence) => $licence->label
            )->toArray() ?? [],

            'createdAt' => $data->createdAt->getTimestamp(),
            'createdAtDate' => $data->createdAt->format('c'),
            'updatedAt' => $data->updatedAt->getTimestamp(),
            'updatedAtDate' => $data->updatedAt->format('c'),
            'publishedAt' => $data->publishedAt?->getTimestamp(),
            'publishedAtDate' => $data->publishedAt?->format('c'),
            'disabledAt' => $data->disabledAt?->getTimestamp(),
            'disabledAtDate' => $data->disabledAt?->format('c'),

            '_geo' => null,
        ];

        // Not sure yet on the details but something can be done with this.
        // https://www.meilisearch.com/docs/learn/filtering_and_sorting/geosearch#sorting-results-with-_geopoint
        $address = $data->operatingAddress() ?? $data->defaultAddress();
        if (isset($address, $address->latitude, $address->longitude)) {
            $document['_geo'] = [
                'lat' => $address->latitude,
                'lng' => $address->longitude,
            ];
        }

        return new Document($document);
    }

    private function mapEmployees(Collection $collection): array
    {
        $output = [];

        /** @var \Lens\Bundle\LensApiBundle\Entity\Company\Employee $employee */
        foreach ($collection as $employee) {
            $personal = $this->mapPersonal($employee->personal);
            if (!empty($personal)) {
                $output[] = [
                    'id' => $employee->id,
                    'function' => $employee->function,
                    'personal' => $personal,
                ];
            }
        }

        return $output;
    }

    private function mapPaymentMethods(Collection $collection): array
    {
        $output = [];
        /** @var \Lens\Bundle\LensApiBundle\Entity\PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($collection as $paymentMethod) {
            if (!($paymentMethod instanceof Debit)) {
                continue;
            }

            $output['debit'] = [
                'account' => $paymentMethod->accountHolder,
                'iban' => $paymentMethod->iban,
            ];
        }

        return $output;
    }

    private function companyFromParameter(object $object): ?Company
    {
        return match ($object::class) {
            Company::class => $object,
            DrivingSchool::class => $object->company,
            Address::class => $object->company,
            ContactMethod::class => $object->company,
            PaymentMethod::class => $object->company,
            Employee::class => $object->company,
            default => throw new LogicException('Doctrine listener received an unexpected entity of type '.get_debug_type($object).'. See '.__CLASS__.' for details.'),
        };
    }
}
