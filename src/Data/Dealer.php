<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\Validator as Validators;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[Validators\UniqueDealer(message: 'dealer.unique_dealer')]
class Dealer
{
    #[Assert\NotBlank(message: 'dealer.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'dealer.name.not_blank')]
    public string $name;

    /** @var null|Company[] */
    public ?array $companies = null;
}
