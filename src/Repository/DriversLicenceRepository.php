<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\DriversLicence;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class DriversLicenceRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('drivers-licences.json', $options)->toArray();

        return $this->api->asArray($response, DriversLicence::class);
    }

    public function get(DriversLicence|Ulid|string $driversLicence, array $options = []): ?DriversLicence
    {
        $response = $this->api->get(sprintf(
            'drivers-licences/%s.json',
            $driversLicence->id ?? $driversLicence,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), DriversLicence::class);
    }

    public function post(DriversLicence $driversLicence, array $options = []): DriversLicence
    {
        $response = $this->api->post('drivers-licences.json', [
            'json' => $driversLicence,
        ] + $options)->toArray();

        return $this->api->as($response, DriversLicence::class);
    }

    public function patch(DriversLicence $driversLicence, array $options = []): DriversLicence
    {
        $url = sprintf('drivers-licences/%s.json', $driversLicence->id);

        $response = $this->api->patch($url, [
            'json' => $driversLicence,
        ] + $options)->toArray();

        return $this->api->as($response, DriversLicence::class);
    }

    public function delete(DriversLicence|Ulid|string $driversLicence, array $options = []): void
    {
        $url = sprintf('drivers-licences/%s.json', $driversLicence->id ?? $driversLicence);

        $this->api->delete($url, $options)->getHeaders();
    }
}
