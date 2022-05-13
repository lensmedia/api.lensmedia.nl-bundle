<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class DriversLicence
{
    #[Assert\NotBlank(message: 'drivers_licence.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'drivers_licence.label.not_blank')]
    public string $label;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
