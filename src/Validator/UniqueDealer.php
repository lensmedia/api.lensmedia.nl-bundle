<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueDealer extends Constraint
{
    public string $message = 'A dealer with the name "{{ value }}" already exists.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
