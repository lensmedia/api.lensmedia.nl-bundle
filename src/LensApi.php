<?php

namespace Lens\Bundle\LensApiBundle;

use Lens\Bundle\LensApiBundle\Repository\AddressRepository;
use Lens\Bundle\LensApiBundle\Repository\CompanyRepository;
use Lens\Bundle\LensApiBundle\Repository\DrivingSchoolRepository;
use Lens\Bundle\LensApiBundle\Repository\DealerRepository;
use Lens\Bundle\LensApiBundle\Repository\PersonalRepository;
use Lens\Bundle\LensApiBundle\Repository\UserRepository;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class LensApi implements HttpClientInterface
{
    private HttpClientInterface $httpClient;

    public AddressRepository $addresses;
    public CompanyRepository $companies;
    public DealerRepository $dealers;
    public DrivingSchoolRepository $drivingSchools;
    public PersonalRepository $personals;
    public UserRepository $users;

    public function __construct(
        private SerializerInterface $serializer,
        HttpClientInterface $httpClient,
        array $options,
    ) {
        $this->httpClient = $httpClient->withOptions($options);

        $this->addresses = new AddressRepository($this);
        $this->companies = new CompanyRepository($this);
        $this->dealers = new DealerRepository($this);
        $this->drivingSchools = new DrivingSchoolRepository($this);
        $this->personals = new PersonalRepository($this);
        $this->users = new UserRepository($this);
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
