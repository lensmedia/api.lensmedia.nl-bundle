<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Company;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class CompanyRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('companies.json', $options)->toArray();

        return $this->api->asArray($response, Company::class);
    }

    public function get(Company|Ulid|string $company, array $options = []): ?Company
    {
        $response = $this->api->get(sprintf(
            'companies/%s.json',
            $company->id ?? $company,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), Company::class);
    }

    public function post(Company $company, array $options = []): Company
    {
        $response = $this->api->post('companies.json', [
            'json' => $company,
        ] + $options)->toArray();

        return $this->api->as($response, Company::class);
    }

    public function patch(Company $company, array $options = []): Company
    {
        $url = sprintf('companies/%s.json', $company->id);

        $response = $this->api->patch($url, [
            'json' => $company,
        ] + $options)->toArray();

        return $this->api->as($response, Company::class);
    }

    public function delete(Company|Ulid|string $company, array $options = []): void
    {
        $url = sprintf('companies/%s.json', $company->id ?? $company);

        $this->api->delete($url, $options)->getHeaders();
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

    /**
     * @deprecated Use `CompanyRepository::get` instead.
     */
    public function byId(Ulid|string $company): ?Company
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::get" instead.', __METHOD__, __CLASS__);

        return $this->get($company);
    }

    /**
     * @deprecated This one was only ment to be used for the initial migration from old to new database.
     */
    public function chamberOfCommerceToId(): array
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s::chamberOfCommerceToId" is deprecated and should not be used for new things, there is no alternative yet.', __METHOD__);

        return $this->api->get('companies/chamber-of-commerce-to-id.json')->toArray();
    }
}
