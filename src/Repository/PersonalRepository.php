<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Personal;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class PersonalRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('personals.json', $options)->toArray();

        return $this->api->asArray($response, Personal::class);
    }

    public function get(Personal|Ulid|string $personal, array $options = []): ?Personal
    {
        $response = $this->api->get(sprintf(
            'personals/%s.json',
            $personal->id ?? $personal,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), Personal::class);
    }

    public function post(Personal $personal, array $options = []): Personal
    {
        $response = $this->api->post('personals.json', [
            'json' => $personal,
        ] + $options)->toArray();

        return $this->api->as($response, Personal::class);
    }

    public function patch(Personal $personal, array $options = []): Personal
    {
        $url = sprintf('personals/%s.json', $personal->id);

        $response = $this->api->patch($url, [
            'json' => $personal,
        ] + $options)->toArray();

        return $this->api->as($response, Personal::class);
    }

    public function delete(Personal|Ulid|string $personal, array $options = []): void
    {
        $url = sprintf('personals/%s.json', $personal->id ?? $personal);

        $this->api->delete($url, $options)->getHeaders();
    }

    /**
     * @deprecated Use `PersonalRepository::get` instead.
     */
    public function byId(string|Ulid $personal): ?Personal
    {
        trigger_deprecation('lensmedia/api.lensmedia.nl-bundle', '*', 'The method "%s" is deprecated, use "%s::get" instead.', __METHOD__, __CLASS__);

        return $this->get($personal);
    }
}
