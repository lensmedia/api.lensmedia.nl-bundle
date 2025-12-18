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
}
