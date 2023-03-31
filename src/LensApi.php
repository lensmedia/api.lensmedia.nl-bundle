<?php

namespace Lens\Bundle\LensApiBundle;

use Lens\Bundle\LensApiBundle\OldApiRepository;
use Lens\Bundle\LensApiBundle\OldApiRepository\LensApiResourceDataInterface;
use RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class LensApi implements HttpClientInterface
{
    private HttpClientInterface $httpClient;

    public readonly OldApiRepository\AddressRepository $addresses;
    public readonly OldApiRepository\AdvertisementRepository $advertisements;
    public readonly OldApiRepository\CompanyRepository $companies;
    public readonly OldApiRepository\DealerRepository $dealers;
    public readonly OldApiRepository\DrivingSchoolRepository $drivingSchools;
    public readonly OldApiRepository\DriversLicenceRepository $driversLicences;
    public readonly OldApiRepository\PaymentMethodRepository $paymentMethods;
    public readonly OldApiRepository\PersonalRepository $personals;
    public readonly OldApiRepository\UserRepository $users;

    public function __construct(
        private readonly SerializerInterface $serializer,
        HttpClientInterface $httpClient,
        array $options,
    ) {
        $this->httpClient = $httpClient->withOptions($options);

        $this->addresses = new OldApiRepository\AddressRepository($this);
        $this->advertisements = new OldApiRepository\AdvertisementRepository($this);
        $this->companies = new OldApiRepository\CompanyRepository($this);
        $this->dealers = new OldApiRepository\DealerRepository($this);
        $this->drivingSchools = new OldApiRepository\DrivingSchoolRepository($this);
        $this->driversLicences = new OldApiRepository\DriversLicenceRepository($this);
        $this->paymentMethods = new OldApiRepository\PaymentMethodRepository($this);
        $this->personals = new OldApiRepository\PersonalRepository($this);
        $this->users = new OldApiRepository\UserRepository($this);
    }

    public function reference(
        LensApiResourceDataInterface|string $lensApiResourceData,
        Ulid|string|null $id = null,
        int $apiVersion = 1,
    ): string {
        $resource = $lensApiResourceData;
        if ($lensApiResourceData instanceof LensApiResourceDataInterface) {
            $resource = $lensApiResourceData::resource();
        } elseif (class_exists($lensApiResourceData)) {
            if (is_a($lensApiResourceData, LensApiResourceDataInterface::class, true)) {
                throw new RuntimeException(sprintf(
                    'Class "%s" does not implement "%s".',
                    $lensApiResourceData,
                    LensApiResourceDataInterface::class,
                ));
            }

            $resource = $lensApiResourceData::resource();
        }

        return sprintf(
            '/v%d/%s/%s',
            $apiVersion,
            $resource,
            $lensApiResourceData->id ?? $id,
        );
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
        return $this->request('GET', $url, array_merge_recursive([
            'headers' => ['content-type' => 'application/ld+json'],
        ], $options));
    }

    public function post(string $url, array $options = []): ResponseInterface
    {
        if (is_object($options['json'])) {
            $options['json'] = $this->serializer->normalize($options['json'], null, [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            ]);
        }

        return $this->request('POST', $url, array_merge_recursive([
            'headers' => ['content-type' => 'application/ld+json'],
        ], $options));
    }

    public function put(string $url, array $options = []): ResponseInterface
    {
        return $this->request('PUT', $url, array_merge_recursive([
            'headers' => ['content-type' => 'application/ld+json'],
        ], $options));
    }

    public function patch(string $url, array $options = []): ResponseInterface
    {
        return $this->request('PATCH', $url, array_merge_recursive([
            'headers' => ['content-type' => 'application/merge-patch+json'],
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
