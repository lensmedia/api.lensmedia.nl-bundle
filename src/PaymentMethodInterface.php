<?php

namespace Lens\Bundle\LensApiBundle;

interface PaymentMethodInterface
{
    public const METHODS = [
        self::DEBIT,
        self::CREDIT_CARD,
    ];

    public const DEBIT = 'debit';
    public const CREDIT_CARD = 'credit_card';
}
