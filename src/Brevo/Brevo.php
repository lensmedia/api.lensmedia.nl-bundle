<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Brevo;

use Brevo\Client\Api\ContactsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Configuration;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\UpdateContact;
use Exception;
use GuzzleHttp\Client;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use SensitiveParameter;

class Brevo
{
    private int $subscriberListId;

    public function __construct(
        #[SensitiveParameter]
        private readonly string $apikey,
        string|int $subscriberListId,
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

        $createContact = new CreateContact()
            ->setEmail($email)
            ->setAttributes((object)[
                'FIRSTNAME' => $this->firstNameFromPersonal($personal),
                'LASTNAME' => $this->lastNameFromPersonal($personal),
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

        $updateContact = new UpdateContact()
            ->setAttributes((object)[
                'EMAIL' => $email,
                'FIRSTNAME' => $this->firstNameFromPersonal($personal),
                'LASTNAME' => $this->lastNameFromPersonal($personal),
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

    private function firstNameFromPersonal(Personal $personal): ?string
    {
        return $personal->initials;
    }

    private function lastNameFromPersonal(Personal $personal): ?string
    {
        return trim($personal->surnameAffix.' '.$personal->surname);
    }
}
