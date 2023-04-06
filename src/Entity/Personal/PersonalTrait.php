<?php

namespace Lens\Bundle\LensApiBundle\Entity\Personal;

use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;

trait PersonalTrait
{
    public function employment(int $offset = 0): ?Employee
    {
        return $this->companies[$offset] ?? null;
    }

    public function company(int $offset = 0): ?Company
    {
        return $this->employment($offset)?->company;
    }
}
