<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueUser extends Constraint
{
    public string $message = 'A user with the name "{{ value }}" already exists.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
