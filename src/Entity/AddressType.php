<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

enum AddressType: string
{
    case Default = 'default';
    case Mailing = 'mailing';
    case Shipping = 'shipping';
    case Billing = 'billing';
    case Operating = 'operating';

    public function isDefault(): bool
    {
        return self::Default === $this;
    }

    public function isMailing(): bool
    {
        return self::Mailing === $this;
    }

    public function isShipping(): bool
    {
        return self::Shipping === $this;
    }

    public function isBilling(): bool
    {
        return self::Billing === $this;
    }

    public function isOperating(): bool
    {
        return self::Operating === $this;
    }
}
