<?php

namespace Lens\Bundle\LensApiBundle;

use Lens\Bundle\LensApiBundle\Data\Address;
use Lens\Bundle\LensApiBundle\Data\Company;
use Lens\Bundle\LensApiBundle\Data\Dealer;
use Lens\Bundle\LensApiBundle\Data\Personal;
use Lens\Bundle\LensApiBundle\Data\User;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class LensApi implements HttpClientInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(
        private SerializerInterface $serializer,
        HttpClientInterface $httpClient,
        array $options = []
    ) {
        $this->httpClient = $httpClient->withOptions($options);
    }

    private function client(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /** Interface implementations */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->client()->request($method, $url, $options);
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->client()->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        return new static(
            $this->serializer,
            $this->client()->withOptions($options),
        );
    }

    /** App aliases */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    public function post(string $url, array $options = []): ResponseInterface
    {
        return $this->request('POST', $url, $options);
    }

    public function put(string $url, array $options = []): ResponseInterface
    {
        return $this->request('PUT', $url, $options);
    }

    public function patch(string $url, array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $url, array_merge_recursive([
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
        ], $options));
    }

    public function delete(string $url, array $options = []): ResponseInterface
    {
        return $this->request('DELETE', $url, $options);
    }

    /**
     * @template T
     *
     * @param array|null      $data
     * @param class-string<T> $className
     *
     * @return ?T
     *
     * @throws ExceptionInterface
     */
    public function as(?array $data, string $className): ?object
    {
        if (null === $data) {
            return null;
        }

        return $this->serializer->denormalize($data, $className);
    }

    /**
     * @template T
     *
     * @param array<int, array> $data
     * @param class-string<T>   $className
     *
     * @return T[]
     *
     * @throws ExceptionInterface
     */
    public function asArray(array $data, string $className): array
    {
        if (empty($data)) {
            return [];
        }

        return $this->serializer->denormalize($data, $className.'[]');
    }

    public function recoverPassword(string|Ulid $user): User
    {
        $response = $this->get(sprintf(
            'users/%s/recover.json',
            (string)$user,
        ))->toArray();

        return $this->as($response, User::class);
    }

    public function recoverPasswordCheckStatus(string|Ulid $user, string $token): User
    {
        $response = $this->get(sprintf(
            'users/%s/recover/%s.json',
            (string)$user,
            $token,
        ))->toArray();

        return $this->as($response, User::class);
    }

    public function recoverUpdatePassword(string|Ulid $user, string $token, string $plainPassword): User
    {
        $response = $this->patch(sprintf(
            'users/%s/recover/%s.json',
            (string)$user,
            $token,
        ), [
            'json' => [
                'id' => $user,
                'plainPassword' => $plainPassword,
            ],
        ])->toArray();

        return $this->as($response, User::class);
    }

    public function userById(string|Ulid $user): User
    {
        $data = $this->get(sprintf(
            'users/%s.json',
            (string)$user,
        ))->toArray();

        return $this->as($data, User::class);
    }

    public function personalById(string|Ulid $personal): Personal
    {
        $data = $this->get(sprintf(
            'personals/%s.json',
            (string)$personal,
        ))->toArray();

        return $this->as($data, Personal::class);
    }

    public function companyById(string|Ulid $company): Company
    {
        $data = $this->get(sprintf(
            'companies/%s.json',
            (string)$company,
        ))->toArray();

        return $this->as($data, Company::class);
    }

    public function drivingSchoolById(string|Ulid $drivingSchool): Company
    {
        $data = $this->get(sprintf(
            'driving-schools/%s.json',
            (string)$drivingSchool,
        ))->toArray();

        return $this->as($data, Company::class);
    }

    public function drivingSchoolByChamberOfCommerce(
        string $chamberOfCommerce,
        bool $chainItemOperation = false,
    ): ?Company {
        $drivingSchool = $this->get('driving-schools.json', [
            'query' => ['chamberOfCommerce' => $chamberOfCommerce],
        ])->toArray()[0] ?? null;

        if (!$drivingSchool) {
            return null;
        }

        if ($chainItemOperation) {
            return $this->drivingSchoolById($drivingSchool['id']);
        }

        return $this->as($drivingSchool, Company::class);
    }

    public function searchDrivingSchool(string $terms): array
    {
        $data = $this->get('driving-schools/search.json', [
            'query' => [
                'q' => $terms,
            ],
        ])->toArray();

        return $this->asArray($data, Company::class);
    }

    public function nearbyDrivingSchools(Company $drivingSchool): array
    {
        $drivingSchools = $this->get(sprintf(
            'driving-schools/%s/nearby.json',
            $drivingSchool->id,
        ))->toArray();

        return $this->asArray(
            $drivingSchools,
            Company::class,
        );
    }

    public function addressById(string|Ulid $address): Address
    {
        $data = $this->get(sprintf(
            'addresses/%s.json',
            (string)$address,
        ))->toArray();

        return $this->as($data, Address::class);
    }

    public function dealersForMap(Dealer $dealer): array
    {
        $dealers = $this->get(sprintf(
            'dealers/%s/map.json',
            $this->iTheorieDealer()->id,
        ))->toArray();

        return $this->asArray($dealers, Company::class);
    }

    public function nearbyDealers(Dealer $dealer, Company $company): array
    {
        $dealers = $this->get(sprintf(
            'dealers/%s/companies/%s/nearby.json',
            $dealer->id,
            $company->id,
        ))->toArray();

        return $this->asArray($dealers, Company::class);
    }

    /**
     * @deprecated This one is only ment to be used for the initial migration.
     */
    public function companiesChamberOfCommerceToId(): array
    {
        return $this->get('companies/chamber-of-commerce-to-id.json')->toArray();
    }
}
