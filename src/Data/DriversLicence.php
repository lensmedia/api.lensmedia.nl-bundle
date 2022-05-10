<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;

class DriversLicence
{
    public Ulid $id;

    public string $label;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
