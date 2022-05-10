<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;

class Advertisement
{
    public Ulid $id;

    public string $type;

    /** @var Personal[] */
    public array $personal = [];

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
