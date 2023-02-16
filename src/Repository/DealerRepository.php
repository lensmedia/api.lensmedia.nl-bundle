<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Company;
use Lens\Bundle\LensApiBundle\Data\Dealer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class DealerRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('dealers.json', $options)->toArray();

        return $this->api->asArray($response, Dealer::class);
    }

    public function get(Dealer|Ulid|string $dealer, array $options = []): ?Dealer
    {
        $response = $this->api->get(sprintf(
            'dealers/%s.json',
            $dealer->id ?? $dealer,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), Dealer::class);
    }

    public function post(Dealer $dealer, array $options = []): Dealer
    {
        $response = $this->api->post('dealers.json', [
            'json' => $dealer,
        ] + $options)->toArray();

        return $this->api->as($response, Dealer::class);
    }

    public function patch(Dealer $dealer, array $options = []): Dealer
    {
        $url = sprintf('dealers/%s.json', $dealer->id);

        $response = $this->api->patch($url, [
            'json' => $dealer,
        ] + $options)->toArray();

        return $this->api->as($response, Dealer::class);
    }

    public function delete(Dealer|Ulid|string $dealer, array $options = []): void
    {
        $url = sprintf('dealers/%s.json', $dealer->id ?? $dealer);

        $this->api->delete($url, $options)->getHeaders();
    }

    /**
     * @deprecated Use `DealerRepository::get` instead.
     */
    public function byId(Ulid|string $dealer): ?Dealer
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::get" instead.', __METHOD__, __CLASS__);

        return $this->get($dealer);
    }

    public function getByName(string $name): ?Dealer
    {
        $response = $this->api->get('dealers.json', [
            'query' => ['name' => $name],
        ])->toArray()[0] ?? null;

        if (!$response) {
            return null;
        }

        return $this->get($response['id']);
    }

    /**
     * @deprecated Use `DealerRepository::getByName` instead.
     */
    public function byName(string $name): ?Dealer
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::getByName" instead.', __METHOD__, __CLASS__);

        return $this->getByName($name);
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
