<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\OldApiRepository\LensApiResourceDataInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentMethod implements LensApiResourceDataInterface
{
    public const DEBIT = 'debit';
    public const TYPES = [
        self::DEBIT => self::DEBIT,
    ];

    #[Assert\NotBlank(message: 'payment_method.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'payment_method.method.not_blank')]
    #[Assert\Choice(choices: [
        self::DEBIT,
    ], message: 'payment_method.method.choice')]
    public string $method = self::DEBIT;

    #[Assert\Valid]
    public Company|string|null $company = null;

    #[Assert\NotBlank(message: 'payment_method.account_holder.not_blank')]
    public string $accountHolder;

    #[Assert\NotBlank(message: 'payment_method.iban.not_blank')]
    #[Assert\Iban(message: 'payment_method.iban.iban')]
    public string $iban;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public static function debit(): PaymentMethod
    {
        $instance = new self();
        $instance->method = self::DEBIT;

        return $instance;
    }

    public static function resource(): string
    {
        return 'payment-methods';
    }
}
