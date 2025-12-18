<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Entity\User;

trait EmployeeTrait
{
    public function employee(int $offset = 0): ?Employee
    {
        return $this->employees[$offset] ?? null;
    }

    public function personal(int $offset = 0): ?Personal
    {
        return $this->employee($offset)?->personal;
    }

    public function user(int $offset = 0): ?User
    {
        return $this->personal($offset)?->user;
    }
}
