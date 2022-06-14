<?php

namespace Lens\Bundle\LensApiBundle\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Initials extends Constraint
{
    public const PATTERN_HTML = '(\p{Lu}\p{Ll}*\.)+';
    public const PATTERN = '/^'.self::PATTERN_HTML.'$/u';

    public string $message = 'Invalid initials format "{{ value }}" use eg; "A.", "A.B." or "A.B.Chr.".';

    public function __construct(
        mixed $options = null,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct($options ?? [], $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
