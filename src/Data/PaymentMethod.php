<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentMethod
{
    public const TYPE_DEBIT = 'debit';
    public const TYPES = [
        self::TYPE_DEBIT => self::TYPE_DEBIT,
    ];

    #[Assert\NotBlank(message: 'payment_method.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'payment_method.type.not_blank')]
    #[Assert\Choice(choices: [
        self::TYPE_DEBIT,
    ], message: 'payment_method.type.choice')]
    public string $type;

    #[Assert\NotBlank(message: 'payment_method.account_holder.not_blank')]
    public ?string $accountHolder = null;

    #[Assert\NotBlank(message: 'payment_method.iban.not_blank')]
    #[Assert\Iban(message: 'payment_method.iban.iban')]
    public ?string $iban = null;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
