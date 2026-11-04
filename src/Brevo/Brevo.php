<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Brevo;

use Brevo\Client\Api\ContactsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateAttribute;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\UpdateAttribute;
use Brevo\Client\Model\UpdateContact;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DriversLicence;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\DriversLicenceRepository;
use SensitiveParameter;

use const JSON_THROW_ON_ERROR;

class Brevo
{
    public const string DRIVERS_LICENCES_ATTRIBUTE = 'RIJBEWIJZEN';

    private const string NORMAL_ATTRIBUTE_CATEGORY = 'normal';

    private int $subscriberListId;

    /** @var string[]|null */
    private ?array $driversLicenceOptions = null;

    public function __construct(
        #[SensitiveParameter]
        private readonly string $apikey,
        string|int $subscriberListId,
        private readonly DriversLicenceRepository $driversLicenceRepository,
    ) {
        $this->subscriberListId = (int)$subscriberListId;
    }

    public function isContact(Personal|string $email): bool
    {
        if ($email instanceof Personal) {
            $email = $email->emailContactMethod()?->value;
        }

        if (!$email) {
            return false;
        }

        try {
            $this->api()->getContactInfo($email);

            return true;
        } catch (Exception) {
        }

        return false;
    }

    /**
     * @throws ApiException on non-2xx response
     */
    public function createContact(Personal $personal): void
    {
        if (!($email = $personal->emailContactMethod()?->value)) {
            return;
        }

        $this->ensureDriversLicencesAttribute();

        $createContact = new CreateContact()
            ->setEmail($email)
            ->setAttributes((object)[
                'FIRSTNAME' => $this->firstNameFromPersonal($personal),
                'LASTNAME' => $this->lastNameFromPersonal($personal),
                self::DRIVERS_LICENCES_ATTRIBUTE => $this->driversLicencesFromPersonal($personal),
            ])
            // Allows invalid called contact creates to pass without a problem.
            ->setUpdateEnabled(true);

        $listData = $this->brevoListContexts($personal);
        $createContact->setListIds($listData['listIds']);

        $this->api()->createContact($createContact);
    }

    /**
     * @throws ApiException on non-2xx response
     */
    public function updateContact(Personal $personal, ?string $oldEmail = null): void
    {
        $email = $personal->emailContactMethod()?->value;
        if (!$email) {
            return;
        }

        $identifier = $oldEmail ?? $email;
        if (!$this->isContact($identifier)) {
            $this->createContact($personal);

            return;
        }

        $this->ensureDriversLicencesAttribute();

        $updateContact = new UpdateContact()
            ->setAttributes((object)[
                'EMAIL' => $email,
                'FIRSTNAME' => $this->firstNameFromPersonal($personal),
                'LASTNAME' => $this->lastNameFromPersonal($personal),
                self::DRIVERS_LICENCES_ATTRIBUTE => $this->driversLicencesFromPersonal($personal),
            ]);

        $listData = $this->brevoListContexts($personal);
        $updateContact->setListIds($listData['listIds']);
        $updateContact->setUnlinkListIds($listData['unlinkListIds']);

        $this->api()->updateContact($updateContact, $identifier);
    }

    /**
     * @throws ApiException on non-2xx response
     */
    public function deleteContact(Personal|string $email): void
    {
        if ($email instanceof Personal) {
            $email = $email->emailContactMethod()?->value;
        }

        if (!$email) {
            return;
        }

        $this->api()->deleteContact($email);
    }

    /**
     * Sends the drivers licences of each personal with one PUT, Brevo's batch endpoint ignores
     * multiple-choice attributes.
     *
     * A personal without a Brevo contact gets one when it allows email advertising, the same rule
     * UpdateBrevoListener keeps by deleting the contact when that permission is withdrawn.
     *
     * @param iterable<Personal> $personals
     *
     * @return int number of contacts updated or created
     *
     * @throws ApiException on non-2xx response other than 404
     */
    public function syncDriversLicences(iterable $personals): int
    {
        $this->ensureDriversLicencesAttribute();

        $synced = 0;
        foreach ($personals as $personal) {
            if (!($email = $personal->emailContactMethod()?->value)) {
                continue;
            }

            try {
                $this->api()->updateContact(
                    new UpdateContact()->setAttributes((object)[
                        self::DRIVERS_LICENCES_ATTRIBUTE => $this->driversLicencesFromPersonal($personal),
                    ]),
                    $email,
                );
            } catch (ApiException $exception) {
                if (404 !== $exception->getCode()) {
                    throw $exception;
                }

                if (!$personal->canAdvertiseByEmail()) {
                    continue;
                }

                $this->createContact($personal);
            }

            ++$synced;
        }

        return $synced;
    }

    /**
     * @throws ApiException on non-2xx response
     */
    public function ensureDriversLicencesAttribute(): void
    {
        $labels = $this->driversLicenceLabels();

        $this->driversLicenceOptions ??= $this->existingDriversLicenceOptions();

        if (null === $this->driversLicenceOptions) {
            $this->api()->createAttribute(
                new CreateAttribute()
                    ->setType(CreateAttribute::TYPE_MULTIPLE_CHOICE)
                    ->setMultiCategoryOptions($labels),
                self::NORMAL_ATTRIBUTE_CATEGORY,
                self::DRIVERS_LICENCES_ATTRIBUTE,
            );

            $this->driversLicenceOptions = $labels;

            return;
        }

        $missingOptions = array_values(array_diff($labels, $this->driversLicenceOptions));
        if (!$missingOptions) {
            return;
        }

        $this->api()->updateAttribute(
            new UpdateAttribute()->setMultiCategoryOptions($missingOptions),
            self::NORMAL_ATTRIBUTE_CATEGORY,
            self::DRIVERS_LICENCES_ATTRIBUTE,
        );

        $this->driversLicenceOptions = [...$this->driversLicenceOptions, ...$missingOptions];
    }

    /**
     * @return string[]|null null when the attribute does not exist
     *
     * @throws ApiException on non-2xx response
     */
    private function existingDriversLicenceOptions(): ?array
    {
        foreach ($this->request('/contacts/attributes')['attributes'] ?? [] as $attribute) {
            if (self::DRIVERS_LICENCES_ATTRIBUTE === $attribute['name'] && self::NORMAL_ATTRIBUTE_CATEGORY === $attribute['category']) {
                return $attribute['multiCategoryOptions'] ?? [];
            }
        }

        return null;
    }

    /**
     * @throws ApiException on non-2xx response
     */
    private function request(string $path, array $query = []): array
    {
        try {
            $response = new client()->request('GET', $this->api()->getConfig()->getHost().$path, [
                'headers' => [
                    'api-key' => $this->apikey,
                    'accept' => 'application/json',
                ],
                'query' => $query,
            ]);

            return json_decode((string)$response->getBody(), true, flags: JSON_THROW_ON_ERROR);
        } catch (GuzzleException|JsonException $exception) {
            throw new ApiException($exception->getMessage(), (int)$exception->getCode());
        }
    }

    private function api(): ContactsApi
    {
        static $api;
        if (empty($api)) {
            $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $this->apikey);

            $api = new ContactsApi(new Client(), $config);
        }

        return $api;
    }

    private function brevoListContexts(Personal $personal): array
    {
        $listContext = [
            'listIds' => [],
            'unlinkListIds' => [],
        ];

        $index = $personal->canAdvertiseByEmail()
            ? 'listIds'
            : 'unlinkListIds';

        $listContext[$index][] = $this->subscriberListId;

        return $listContext;
    }

    /**
     * @return string[]
     */
    private function driversLicenceLabels(): array
    {
        return array_values(array_unique(array_map(
            static fn (DriversLicence $driversLicence) => mb_strtoupper($driversLicence->label),
            $this->driversLicenceRepository->findAll(),
        )));
    }

    /**
     * @return string[]
     */
    private function driversLicencesFromPersonal(Personal $personal): array
    {
        $labels = [];
        foreach ($personal->companies as $employee) {
            $company = $employee->company;
            if ($company->isDisabled() || !$company->drivingSchool) {
                continue;
            }

            foreach ($company->drivingSchool->driversLicences as $driversLicence) {
                $labels[] = mb_strtoupper($driversLicence->label);
            }
        }

        $labels = array_values(array_unique($labels));
        sort($labels);

        return $labels;
    }

    private function firstNameFromPersonal(Personal $personal): ?string
    {
        return $personal->initials;
    }

    private function lastNameFromPersonal(Personal $personal): ?string
    {
        return trim($personal->surnameAffix.' '.$personal->surname);
    }
}
