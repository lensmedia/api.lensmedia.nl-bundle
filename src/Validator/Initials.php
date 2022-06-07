<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Initials extends Constraint
{
    public const PATTERN = '~^(\p{Lu}\p{Ll}*\.)+$~u';

    public string $message = 'Invalid initials format "{{ value }}" use eg; "A.", "A.B." or "A.Chr.".';
}
