<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Company;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class DrivingSchoolRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('driving-schools.json', $options)->toArray();

        return $this->api->asArray($response, Company::class);
    }

    public function get(Company|Ulid|string $drivingSchool, array $options = []): ?Company
    {
        $response = $this->api->get(sprintf(
            'driving-schools/%s.json',
            $drivingSchool->id ?? $drivingSchool,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), Company::class);
    }

    public function post(Company $drivingSchool, array $options = []): Company
    {
        $response = $this->api->post('driving-schools.json', [
            'json' => $drivingSchool,
        ] + $options)->toArray();

        return $this->api->as($response, Company::class);
    }

    public function patch(Company $drivingSchool, array $options = []): Company
    {
        $url = sprintf('driving-schools/%s.json', $drivingSchool->id);

        $response = $this->api->patch($url, [
            'json' => $drivingSchool,
        ] + $options)->toArray();

        return $this->api->as($response, Company::class);
    }

    public function delete(Company|Ulid|string $drivingSchool, array $options = []): void
    {
        $url = sprintf('driving-schools/%s.json', $drivingSchool->id ?? $drivingSchool);

        $this->api->delete($url, $options)->getHeaders();
    }

    public function search(string $terms): array
    {
        $response = $this->api->get('driving-schools/search.json', [
            'query' => [
                'q' => $terms,
            ],
        ])->toArray();

        return $this->api->asArray($response, Company::class);
    }

    /**
     * @deprecated Use `DrivingSchoolRepository::get` instead.
     */
    public function byId(string|Ulid $drivingSchool): Company
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::get" instead.', __METHOD__, __CLASS__);

        return $this->get($drivingSchool);
    }

    public function getByCbr(string $cbr): ?Company
    {
        $response = $this->api->get('driving-schools.json', [
            'query' => ['cbr' => $cbr],
        ])->toArray()[0] ?? null;

        if (!$response) {
            return null;
        }

        return $this->get($response['id']);
    }

    /**
     * @deprecated Use `DrivingSchoolRepository::getByCbr` instead.
     */
    public function byCbr(string $cbr): ?Company
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::getByCbr" instead.', __METHOD__, __CLASS__);

        return $this->getByCbr($cbr);
    }

    public function getByChamberOfCommerce(string $chamberOfCommerce): ?Company
    {
        $response = $this->api->get('driving-schools.json', [
            'query' => ['chamberOfCommerce' => $chamberOfCommerce],
        ])->toArray()[0] ?? null;

        if (!$response) {
            return null;
        }

        return $this->get($response['id']);
    }

    /**
     * @deprecated Use `DrivingSchoolRepository::getByChamberOfCommerce` instead.
     */
    public function byChamberOfCommerce(string $chamberOfCommerce): ?Company
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::getByChamberOfCommerce" instead.', __METHOD__, __CLASS__);

        return $this->getByChamberOfCommerce($chamberOfCommerce);
    }

    public function nearby(Company|Ulid|string $drivingSchool): array
    {
        $response = $this->api->get(sprintf(
            'driving-schools/%s/nearby.json',
            $drivingSchool->id ?? $drivingSchool,
        ))->toArray();

        return $this->api->asArray($response, Company::class);
    }
}
