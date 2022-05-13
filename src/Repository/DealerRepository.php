<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Company;
use Lens\Bundle\LensApiBundle\Data\Dealer;
use Symfony\Component\Validator\Constraints\Ulid;

class DealerRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get(
            'dealers.json',
            $options,
        )->toArray();

        return $this->api->asArray($response, Dealer::class);
    }

    public function byId(Ulid|string $dealer): ?Dealer
    {
        $response = $this->api->get(sprintf(
            'dealers/%s.json',
            $dealer
        ))->toArray();

        return $this->api->as(
            $response,
            Dealer::class,
        );
    }

    public function byName(string $name): ?Dealer
    {
        $response = $this->api->get('dealers.json', [
                'query' => ['name' => $name],
            ])->toArray()[0] ?? null;

        if (!$response) {
            return null;
        }

        return $this->byId($response['id']);
    }

    public function companies(Dealer $dealer, array $options = []): array
    {
        $response = $this->api->get(sprintf(
            'dealers/%s/companies.json',
            $dealer->id,
        ), $options)->toArray();

        return $this->api->asArray($response, Company::class);
    }

    public function map(Dealer $dealer): array
    {
        $response = $this->api->get(sprintf(
            'dealers/%s/map.json',
            $dealer->id,
        ))->toArray();

        return $this->api->asArray($response, Company::class);
    }

    public function nearby(Dealer $dealer, Company $company): array
    {
        $response = $this->api->get(sprintf(
            'dealers/%s/companies/%s/nearby.json',
            $dealer->id,
            $company->id,
        ))->toArray();

        return $this->api->asArray($response, Company::class);
    }
}
