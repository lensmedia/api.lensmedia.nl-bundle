<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\Repository\LensApiResourceDataInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Employee implements LensApiResourceDataInterface
{
    #[Assert\NotBlank(message: 'employee.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'employee.function.not_blank')]
    public string $function;

    #[Assert\Valid]
    public Personal|string|null $personal = null;

    #[Assert\Valid]
    public Company|string|null $company = null;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public static function resource(): string
    {
        return 'employees';
    }
}
