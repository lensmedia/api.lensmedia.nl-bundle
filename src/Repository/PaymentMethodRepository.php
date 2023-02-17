<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\Data\PaymentMethod;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Ulid;

class PaymentMethodRepository extends AbstractRepository
{
    public function list(array $options = []): array
    {
        $response = $this->api->get('payment-methods.json', $options)->toArray();

        return $this->api->asArray($response, PaymentMethod::class);
    }

    public function get(PaymentMethod|Ulid|string $paymentMethod, array $options = []): ?PaymentMethod
    {
        $response = $this->api->get(sprintf(
            'payment-methods/%s.json',
            $paymentMethod->id ?? $paymentMethod,
        ), $options);

        if (Response::HTTP_NOT_FOUND === $response->getStatusCode()) {
            return null;
        }

        return $this->api->as($response->toArray(), PaymentMethod::class);
    }

    public function post(PaymentMethod $paymentMethod, array $options = []): PaymentMethod
    {
        $url = sprintf('%s.json', $this->target($paymentMethod));

        $response = $this->api->post($url, [
            'json' => $paymentMethod,
        ] + $options)->toArray();

        return $this->api->as($response, PaymentMethod::class);
    }

    public function patch(PaymentMethod $paymentMethod, array $options = []): PaymentMethod
    {
        $url = sprintf('%s/%s.json', $this->target($paymentMethod), $paymentMethod->id);

        $response = $this->api->patch($url, [
            'json' => $paymentMethod,
        ] + $options)->toArray();

        return $this->api->as($response, PaymentMethod::class);
    }

    public function delete(PaymentMethod|Ulid|string $paymentMethod, array $options = []): void
    {
        $url = sprintf('payment-methods/%s.json', $paymentMethod->id ?? $paymentMethod);

        $this->api->delete($url, $options)->getHeaders();
    }

    private function target(PaymentMethod $paymentMethod): string
    {
        return match ($paymentMethod->method) {
            PaymentMethod::DEBIT => 'debits',
            default => 'payment-methods',
        };
    }
}
