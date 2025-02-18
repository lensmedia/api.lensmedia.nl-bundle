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
            new Index(
                uid: 'company',
                client: 'lens_api',
            ),
        ];
    }

    public function toDocument(object $data, array $context = []): Document
    {
        if (!($data instanceof Company)) {
            throw new InvalidTransformData($data, Company::class);
        }

        return new Document([
            'id' => $data->id,
            'type' => $data->isDrivingSchool() ? 'driving_school' : 'company',
            'name' => $data->name,
            'chamber_of_commerce' => $data->chamberOfCommerce,
            // 'affiliate' => $data->affiliate,
            'customer_number' => $data->customerNumber(),

            'addresses' => $this->mapAddresses($data->addresses),
            'contact' => $this->mapContactMethods($data->contactMethods),

            'payment' => $this->mapPaymentMethods($data->paymentMethods),
            'employees' => $this->mapEmployees($data->employees),
            'dealer' => $this->mapDealers($data->dealers),

            'cbr' => $data->drivingSchool?->cbr,
            'licenses' => $data->drivingSchool?->driversLicences->map(fn (DriversLicence $licence) => $licence->label)->toArray() ?? [],

            'created_at' => $data->createdAt,
            'updated_at' => $data->updatedAt,
            'published_at' => $data->publishedAt,
            'disabled_at' => $data->disabledAt,

            // Not sure yet on the details but something can be done with this.
            // https://www.meilisearch.com/docs/learn/filtering_and_sorting/geosearch#sorting-results-with-_geopoint
            '_geo' => [
                'lat' => ($data->operatingAddress() ?? $data->defaultAddress())?->latitude,
                'lng' => ($data->operatingAddress() ?? $data->defaultAddress())?->longitude,
            ],
        ]);
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
