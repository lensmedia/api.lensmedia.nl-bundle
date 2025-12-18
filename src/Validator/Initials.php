<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Initials extends Constraint
{
    public const string PATTERN_HTML = '(\p{Lu}\p{Ll}*\.)+';
    public const string PATTERN = '/^'.self::PATTERN_HTML.'$/u';

    public function __construct(
        mixed $options = null,
        public string $message = 'Invalid initials format "{{ value }}" use eg; "A.", "A.B." or "A.B.Chr.".',
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);
    }
}
