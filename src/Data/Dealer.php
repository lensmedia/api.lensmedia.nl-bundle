<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;

class Dealer
{
    public Ulid $id; // 01FWGBYM1VGJ9NDC5WG2SDDT56

    public string $name; // itheorie

    /** @var null|Company[] */
    public ?array $companies = null;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
