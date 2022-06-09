<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentMethod
{
    public const DEBIT = 'debit';
    public const TYPES = [
        self::DEBIT => self::DEBIT,
    ];

    #[Assert\NotBlank(message: 'payment_method.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'payment_method.type.not_blank')]
    #[Assert\Choice(choices: [
        self::DEBIT,
    ], message: 'payment_method.type.choice')]
    public string $type = self::DEBIT;

    #[Assert\NotBlank(message: 'payment_method.account_holder.not_blank')]
    public string $accountHolder;

    #[Assert\NotBlank(message: 'payment_method.iban.not_blank')]
    #[Assert\Iban(message: 'payment_method.iban.iban')]
    public string $iban;
}
