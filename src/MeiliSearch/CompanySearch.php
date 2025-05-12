<?php

namespace Lens\Bundle\LensApiBundle\MeiliSearch;

use Doctrine\Common\Collections\Collection;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DriversLicence;
use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\Debit;
use Lens\Bundle\MeiliSearchBundle\Document;
use Lens\Bundle\MeiliSearchBundle\Exception\InvalidTransformData;
use Lens\Bundle\MeiliSearchBundle\Attribute\Index;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearchDocumentLoaderInterface;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearchIndexLoaderInterface;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

readonly class CompanySearch implements LensMeiliSearchIndexLoaderInterface, LensMeiliSearchDocumentLoaderInterface
{
    public function getIndexes(): array
    {
        return [
            new Index(uid: 'company', settings: [
                'filterableAttributes'=> [
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
            'dealer' => $this->mapDealers($data->dealers),

            'cbr' => $data->drivingSchool?->cbr,
            'licenses' => $data->drivingSchool?->driversLicences->map(fn (DriversLicence $licence) => $licence->label)->toArray() ?? [],

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

    public function supports(): array
    {
        return [Company::class];
    }

    private function mapAddresses(Collection $collection): array
    {
        $output = [];
        /** @var \Lens\Bundle\LensApiBundle\Entity\Address $address */
        foreach ($collection as $address) {
            $output[$address->type] = $address->streetName.' '.trim($address->streetNumber .' '.$address->addition).', '.$address->zipCode.' '.$address->city;
        }

        return $output;
    }

    private function mapContactMethods(Collection $collection): array
    {
        static $phoneNumberUtil;
        if (empty($phoneNumberUtil)) {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
        }

        $output = [];
        /** @var \Lens\Bundle\LensApiBundle\Entity\ContactMethod $contactMethod */
        foreach ($collection as $contactMethod) {
            if ($contactMethod->isPhone()) {
                $number = $phoneNumberUtil->parse($contactMethod->value, 'nl');

                $output[$contactMethod->method] = [
                    'national' => $phoneNumberUtil->format($number, PhoneNumberFormat::NATIONAL),
                    'international' => $phoneNumberUtil->format($number, PhoneNumberFormat::INTERNATIONAL),
                ];
            } elseif ($contactMethod->isSocial()) {
                if (!isset($output[$contactMethod->method])) {
                    $output[$contactMethod->method] = [];
                }

                if (null === $contactMethod->label || '' === $contactMethod->label) {
                    continue;
                }

                $output[$contactMethod->method][$contactMethod->label] = $contactMethod->value;
            } else {
                $output[$contactMethod->method] = $contactMethod->value;
            }
        }

        return $output;
    }

    private function mapEmployees(Collection $collection): array
    {
        $output = [];

        /** @var \Lens\Bundle\LensApiBundle\Entity\Company\Employee $employee */
        foreach ($collection as $employee) {
            $entry = [];

            $personal = $employee->personal;

            $name = trim(implode(' ', [
                $personal->initials,
                $personal->nickname,
                trim($personal->surnameAffix.' '.$personal->surname),
            ]));

            if (!empty($name)) {
                $entry['name'] = $name;
            }

            $contactMethods = $this->mapContactMethods($personal->contactMethods);
            if (!empty($contactMethods)) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $entry = array_merge($entry, $contactMethods);
            }

            if (!empty($entry)) {
                $output[] = $entry;
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

    private function mapDealers(Collection $dealers): iterable
    {
        $output = [];
        foreach ($dealers as $dealer) {
            $output[] = $dealer->name;
        }

        return $output;
    }
}
