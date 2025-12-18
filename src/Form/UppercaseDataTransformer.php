<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Form;

use InvalidArgumentException;
use Symfony\Component\Form\DataTransformerInterface;

readonly class UppercaseDataTransformer implements DataTransformerInterface
{
    public function __construct(
        private bool $transform = true,
        private bool $reverseTransform = true,
    ) {
    }

    /** @param ?string $value */
    public function transform(mixed $value): ?string
    {
        if (!$this->transform) {
            return $value;
        }

        return $this->transformParam($value);
    }

    /** @param ?string $value */
    public function reverseTransform(mixed $value): ?string
    {
        if (!$this->reverseTransform) {
            return $value;
        }

        return $this->transformParam($value);
    }

    private function transformParam(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects values to be null or a string, got "%s".',
                self::class,
                get_debug_type($value)
            ));
        }

        return strtoupper($value);
    }
}
