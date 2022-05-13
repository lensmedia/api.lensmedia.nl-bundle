<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueAdvertisement extends Constraint
{
    public string $message = 'An advertisement option with the name "{{ value }}" already exists.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
