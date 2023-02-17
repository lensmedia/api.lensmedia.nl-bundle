<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\Repository\LensApiResourceDataInterface;
use Lens\Bundle\LensApiBundle\Validator as Validators;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[Validators\UniqueDealer(message: 'dealer.unique_dealer')]
class Dealer implements LensApiResourceDataInterface
{
    #[Assert\NotBlank(message: 'dealer.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'dealer.name.not_blank')]
    public string $name;

    /** @var null|Company[] */
    public ?array $companies = null;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public static function resource(): string
    {
        return 'dealers';
    }
}
