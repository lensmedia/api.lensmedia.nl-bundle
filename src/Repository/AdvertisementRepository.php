<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\Advertisement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class AdvertisementRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('advertisements.json', $options)->toArray();

        return $this->api->asArray($response, Advertisement::class);
    }

    public function get(Advertisement|Ulid|string $advertisement, array $options = []): ?Advertisement
    {
        $response = $this->api->get(sprintf(
            'advertisements/%s.json',
            $advertisement->id ?? $advertisement,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), Advertisement::class);
    }

    public function post(Advertisement $advertisement, array $options = []): Advertisement
    {
        $response = $this->api->post('advertisements.json', [
                'json' => $advertisement,
            ] + $options)->toArray();

        return $this->api->as($response, Advertisement::class);
    }

    public function patch(Advertisement $advertisement, array $options = []): Advertisement
    {
        $url = sprintf('advertisements/%s.json', $advertisement->id);

        $response = $this->api->patch($url, [
            'json' => $advertisement,
        ] + $options)->toArray();

        return $this->api->as($response, Advertisement::class);
    }

    public function delete(Advertisement|Ulid|string $advertisement, array $options = []): void
    {
        $url = sprintf('advertisements/%s.json', $advertisement->id ?? $advertisement);

        $this->api->delete($url, $options)->getHeaders();
    }
}
