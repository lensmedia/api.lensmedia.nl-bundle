<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ChamberOfCommerce extends Constraint
{
    public string $message = '"{{ chamberOfCommerce }}" is an invalid chamber of commerce id.';
}
