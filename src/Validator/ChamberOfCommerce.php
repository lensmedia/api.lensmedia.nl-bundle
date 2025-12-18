<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ChamberOfCommerce extends Constraint
{
    public function __construct(
        mixed $options = null,
        public string $message = '"{{ value }}" is an invalid chamber of commerce id.',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);
    }
}
