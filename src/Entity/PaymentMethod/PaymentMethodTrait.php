<?php

namespace Lens\Bundle\LensApiBundle\Entity\PaymentMethod;

trait PaymentMethodTrait
{
    public function directDebitPaymentMethod(): ?Debit
    {
        return $this->paymentMethods->findFirst(
            static fn (int $index, PaymentMethod $paymentMethod) => $paymentMethod instanceof Debit,
        );
    }
}
