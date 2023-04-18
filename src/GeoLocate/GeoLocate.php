<?php

namespace Lens\Bundle\LensApiBundle\GeoLocate;

use Lens\Bundle\LensApiBundle\Entity\Address;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class GeoLocate
{
    private static array $tracker = [];

    public const API = 'https://geodata.nationaalgeoregister.nl/locatieserver/v3';

    public function __construct(
        private readonly HttpClientInterface $geoApiClient,
    ) {
    }

    public function __invoke(Address $address): array
    {
        if (empty($address->zipCode)) {
            return [null, null];
        }

        $searchTerm = $address->zipCode;
        if ($address->streetNumber) {
            $searchTerm .= 'and '.$address->streetNumber.$address->addition;
        }

        return $this->request($searchTerm)
            ?? $this->request($address->zipCode)
            ?? [null, null];
    }

    private function request(string $query): ?array
    {
        if (isset(self::$tracker[$query])) {
            return self::$tracker[$query];
        }

        try {
            $result = $this->match($this->geoApiClient->request('GET', self::API.'/free', [
                'query' => [
                    'q' => $query,
                ],
            ])->toArray());
        } catch (Throwable $e) {
            throw new GeoLocateException('GeoLocate Error: '.$e->getMessage(), previous: $e);
        }

        self::$tracker[$query] = $result;

        return $result;
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
