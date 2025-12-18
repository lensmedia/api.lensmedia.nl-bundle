<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\GeoLocate;

use Lens\Bundle\LensApiBundle\Entity\Address;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

use function sprintf;

class GeoLocate
{
    private const API = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free';

    public function __construct(
        private readonly HttpClientInterface $geoApiClient,
    ) {
    }

    public function locate(string $query): array
    {
        static $requests = [];
        if (isset($requests[$query])) {
            return $requests[$query];
        }

        try {
            return $requests[$query] = $this->geoApiClient->request('GET', self::API, [
                'query' => [
                    'q' => $query,
                    'rows' => 1,
                ],
            ])->toArray();
        } catch (Throwable $e) {
            throw new GeoLocateException('GeoLocate Error: '.$e->getMessage(), previous: $e);
        }
    }

    public function latLong(string $query): ?array
    {
        $response = $this->locate($query);

        return $this->latLongFromResponse($response);
    }

    public function latLongFromResponse(array $response): ?array
    {
        $docs = $response['response']['docs'][0] ?? null;
        if ($docs && preg_match('/POINT\((\d+\.\d+) (\d+\.\d+)\)/', $docs['centroide_ll'], $matches)) {
            // POINT is in lon lat, swappage around for lat long as is normal?
            return [$matches[2], $matches[1]];
        }

        return null;
    }

    public function latLongFromAddress(Address $address): array
    {
        if (empty($address->zipCode)) {
            return [null, null];
        }

        if ($address->streetNumber) {
            $searchTerm = sprintf(
                'postcode:%s AND huisnummer:%s %s AND type:adres',
                $address->zipCode,
                $address->streetNumber,
                $address->addition,
            );

            $result = $this->latLong($searchTerm);
            if ($result) {
                return $result;
            }
        }

        return $this->latLong(sprintf(
            'postcode: %s AND type:postcode',
            $address->zipCode,
        )) ?? [null, null];
    }
}
