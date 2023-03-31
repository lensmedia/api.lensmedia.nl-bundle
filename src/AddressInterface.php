<?php

namespace Lens\Bundle\LensApiBundle;

interface AddressInterface
{
    public const DEFAULT = 'default';
    public const MAILING = 'mailing';
    public const SHIPPING = 'shipping';
    public const BILLING = 'billing';
    public const OPERATING = 'operating';
}
