<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class UniqueAdvertisement extends Constraint
{
    public function __construct(
        mixed $options = null,
        public string $message = 'An advertisement option with the name "{{ value }}" already exists.',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);
    }
}
