<?php

namespace Lens\Bundle\LensApiBundle\Entity;

trait AddressTrait
{
    public function defaultAddress(): ?Address
    {
        return $this->addresses->findFirst(static fn (int $index, Address $address) => $address->isDefault());
    }

    public function billingAddress(): ?Address
    {
        return $this->addresses->findFirst(static fn (int $index, Address $address) => $address->isBilling());
    }

    public function mailingAddress(): ?Address
    {
        return $this->addresses->findFirst(static fn (int $index, Address $address) => $address->isMailing());
    }

    public function shippingAddress(): ?Address
    {
        return $this->addresses->findFirst(static fn (int $index, Address $address) => $address->isShipping());
    }

    public function operatingAddress(): ?Address
    {
        return $this->addresses->findFirst(static fn (int $index, Address $address) => $address->isOperating());
    }

    public function operatingCoords(): ?array
    {
        if ($address = $this->operatingAddress()) {
            if (null === $address->latitude || null === $address->longitude) {
                return null;
            }

            return [$address->latitude, $address->longitude];
        }

        if ($address = $this->defaultAddress()) {
            if (null === $address->latitude || null === $address->longitude) {
                return null;
            }

            return [$address->latitude, $address->longitude];
        }

        return null;
    }
}
