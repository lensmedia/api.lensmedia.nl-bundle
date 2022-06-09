<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Employee
{
    #[Assert\NotBlank(message: 'employee.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'employee.function.not_blank')]
    public string $function;

    #[Assert\Valid]
    public ?Personal $personal = null;

    #[Assert\Valid]
    public ?Company $company = null;
}
