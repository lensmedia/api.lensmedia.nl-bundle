<?php

namespace Lens\Bundle\LensApiBundle;

use Lens\Bundle\LensApiBundle\Repository;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class LensApi implements HttpClientInterface
{
    private HttpClientInterface $httpClient;

    public readonly Repository\AddressRepository $addresses;
    public readonly Repository\AdvertisementRepository $advertisements;
    public readonly Repository\CompanyRepository $companies;
    public readonly Repository\DealerRepository $dealers;
    public readonly Repository\DrivingSchoolRepository $drivingSchools;
    public readonly Repository\DriversLicenceRepository $driversLicences;
    public readonly Repository\PaymentMethodRepository $paymentMethods;
    public readonly Repository\PersonalRepository $personals;
    public readonly Repository\UserRepository $users;

    public function __construct(
        private readonly SerializerInterface $serializer,
        HttpClientInterface $httpClient,
        array $options,
    ) {
        $this->httpClient = $httpClient->withOptions($options);

        $this->addresses = new Repository\AddressRepository($this);
        $this->advertisements = new Repository\AdvertisementRepository($this);
        $this->companies = new Repository\CompanyRepository($this);
        $this->dealers = new Repository\DealerRepository($this);
        $this->drivingSchools = new Repository\DrivingSchoolRepository($this);
        $this->driversLicences = new Repository\DriversLicenceRepository($this);
        $this->paymentMethods = new Repository\PaymentMethodRepository($this);
        $this->personals = new Repository\PersonalRepository($this);
        $this->users = new Repository\UserRepository($this);
    }

    /** Interface implementations */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->httpClient->request($method, $url, $options);
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        return $this->httpClient->stream($responses, $timeout);
    }

    public function withOptions(array $options): static
    {
        return new static(
            $this->serializer,
            $this->httpClient,
            $options,
        );
    }

    /** custom helper aliases */
    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    public function post(string $url, array $options = []): ResponseInterface
    {
        if (is_object($options['json'])) {
            $options['json'] = $this->serializer->normalize($options['json'], null, [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            ]);
        }

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
}
