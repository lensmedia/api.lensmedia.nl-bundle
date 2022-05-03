<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;

class Employee
{
    public Ulid $id; // 01FWGBYM1VGJ9NDC5WG2SDDT56

    public string $function;

    public ?Personal $personal = null;

    public ?Company $company = null;
}
