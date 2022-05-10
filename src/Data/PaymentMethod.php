<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;

class PaymentMethod
{
    public const TYPE_DEBIT = 'debit';
    public const TYPE_CREDITCARD = 'creditcard';

    public Ulid $id; // 01FWGBYM1VGJ9NDC5WG2SDDT56

    public string $type; // debit

    public ?string $accountHolder = null; // Autorijschool Kruidhof

    public ?string $iban = null; // NL05RABO0345587588

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
