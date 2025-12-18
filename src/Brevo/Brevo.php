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
use Lens\Bundle\LensApiBundle\Entity\Company\Dealer;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\LensApi;
use RuntimeException;
use SensitiveParameter;

class Brevo
{
    private int $subscriberListId;
    private array $subscriberDealerListIds;

    public function __construct(
        private readonly LensApi $lensApi,
        #[SensitiveParameter]
        private readonly string $apikey,
        string|int $subscriberListId,
        string $subscriberDealerListIds,
    ) {
        $this->subscriberListId = (int)$subscriberListId;
        $this->subscriberDealerListIds = $this->mapDealerListIdsFromEnv($subscriberDealerListIds);
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

        $this->api()->updateContact($identifier, $updateContact);
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

    private function mapDealerListIdsFromEnv(string $subscriberDealerListIds): array
    {
        if (!preg_match('/^(([a-z_]+:\d+),)*([a-z_]+:\d+)$/', $subscriberDealerListIds)) {
            throw new RuntimeException('Subscriber dealer list values must match the format "dealer:1,other_dealer:2". And the dealer name must match those from the Dealer entity records for it to work.');
        }

        $subscriberDealerListIds = explode(',', $subscriberDealerListIds);
        if (empty($subscriberDealerListIds)) {
            return [];
        }

        $output = [];
        foreach ($subscriberDealerListIds as $subscriberDealerListId) {
            @[$dealerName, $listId] = explode(':', $subscriberDealerListId);
            $output[$dealerName] = (int)$listId;
        }

        return $output;
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

        // Manage the global list
        $canAdvertiseByEmail = $personal->canAdvertiseByEmail();
        $listContext[$canAdvertiseByEmail ? 'listIds' : 'unlinkListIds'][] = $this->subscriberListId;

        // Manage the dealer specific lists
        $company = $personal->company();
        if (!$company) {
            return $listContext;
        }

        foreach ($this->lensApi->dealers->findAll() as $dealer) {
            $listId = $this->subscriberDealerListId($dealer);
            if (0 === $listId) {
                continue;
            }

            // If we can't advertise, remove from all lists
            if (!$canAdvertiseByEmail) {
                $listContext['unlinkListIds'][] = $listId;
                continue;
            }

            // Check if the person is a dealer
            if ($company->dealers->contains($dealer)) {
                $listContext['listIds'][] = $listId;
            } else {
                $listContext['unlinkListIds'][] = $listId;
            }
        }

        return $listContext;
    }

    private function subscriberDealerListId(Dealer $dealer): int
    {
        return $this->subscriberDealerListIds[$dealer->name] ?? 0;
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
