<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cbr extends Constraint
{
    public string $message = '"{{ cbr }}" is an invalid CBR id, a valid CBR id consists of 4 numbers a letter and a number (eg; 1234A5).';
}
