<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Company;
use Symfony\Component\Uid\Ulid;

class CompanyRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('companies.json', $options)->toArray();

        return $this->api->asArray($response, Company::class);
    }

    public function search(string $terms, array $options = []): array
    {
        $options = array_merge($options, [
            'query' => [
                'q' => $terms,
            ],
        ]);

        $companies = $this->api->get('companies/search.json', $options)
            ->toArray();

        return $this->api->asArray($companies, Company::class);
    }

    public function byId(Ulid|string $user): ?Company
    {
        $response = $this->api->get(sprintf(
            'companies/%s.json',
            $user,
        ))->toArray();

        return $this->api->as($response, Company::class);
    }

    /**
     * @deprecated This one is only ment to be used for the initial migration.
     */
    public function chamberOfCommerceToId(): array
    {
        return $this->api->get('companies/chamber-of-commerce-to-id.json')->toArray();
    }
}
