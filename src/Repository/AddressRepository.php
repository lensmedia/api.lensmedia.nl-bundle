<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Address;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class AddressRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('addresses.json', $options)->toArray();

        return $this->api->asArray($response, Address::class);
    }

    public function get(Address|Ulid|string $address, array $options = []): ?Address
    {
        $response = $this->api->get(sprintf(
            'addresses/%s.json',
            $address->id ?? $address,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), Address::class);
    }

    public function post(Address $address, array $options = []): Address
    {
        $response = $this->api->post('addresses.json', [
            'json' => $address,
        ] + $options)->toArray();

        return $this->api->as($response, Address::class);
    }

    public function patch(Address $address, array $options = []): Address
    {
        $url = sprintf('addresses/%s.json', $address->id);

        $response = $this->api->patch($url, [
            'json' => $address,
        ] + $options)->toArray();

        return $this->api->as($response, Address::class);
    }

    public function delete(Address|Ulid|string $address, array $options = []): void
    {
        $url = sprintf('addresses/%s.json', $address->id ?? $address);

        $this->api->delete($url, $options)->getHeaders();
    }

    /**
     * @deprecated Use `AddressRepository::get` instead.
     */
    public function byId(string|Ulid $address): Address
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::get" instead.', __METHOD__, __CLASS__);

        return $this->get($address);
    }
}
