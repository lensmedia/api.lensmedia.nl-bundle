<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\Repository\LensApiResourceDataInterface;
use Lens\Bundle\LensApiBundle\Validator as Validators;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[Validators\UniqueAdvertisement(message: 'advertisement.unique_advertisement')]
class Advertisement implements LensApiResourceDataInterface
{
    #[Assert\NotBlank(message: 'advertisement.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'advertisement.type.not_blank')]
    public string $type;

    /** @var Personal[] */
    public array $personals = [];

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public static function resource(): string
    {
        return 'advertisements';
    }
}
