<?php

namespace Lens\Bundle\LensApiBundle\GeoLocate;

use Lens\Bundle\LensApiBundle\Entity\Address;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class GeoLocate
{
    private const API = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free';

    public function __construct(
        private readonly HttpClientInterface $geoApiClient,
    ) {
    }

    public function __invoke(Address $address): array
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

            $result = $this->request($searchTerm);
            if ($result) {
                return $result;
            }
        }

        return $this->request('postcode:'.$address->zipCode.' AND type:postcode') ?? [null, null];
    }

    private function request(string $query): ?array
    {
        static $tracker = [];
        if (isset($tracker[$query])) {
            return $tracker[$query];
        }

        try {
            $response = $this->geoApiClient->request('GET', self::API, [
                'query' => [
                    'q' => $query,
                    'rows' => 1,
                ],
            ])->toArray();

            return $tracker[$query] = $this->match($response);
        } catch (Throwable $e) {
            $tracker[$query] = null;

            throw new GeoLocateException('GeoLocate Error: '.$e->getMessage(), previous: $e);
        }
    }

    private function match(array $response): ?array
    {
        $docs = $response['response']['docs'][0] ?? null;
        if ($docs && preg_match('/POINT\((\d+\.\d+) (\d+\.\d+)\)/', $docs['centroide_ll'], $matches)) {
            // POINT is in lon lat, swappage around for lat long as is normal?
            return [$matches[2], $matches[1]];
        }

        return null;
    }
}
