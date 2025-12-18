<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cbr extends Constraint
{
    public function __construct(
        mixed $options = null,
        public string $message = '"{{ value }}" is an invalid CBR id, a valid CBR id consists of 4 numbers a letter and a number (eg; 1234A5).',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);
    }
}
