<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

trait OriginTrait
{
    public function isOrigin(array $options, ...$classes): bool
    {
        return in_array($options['root'], $classes, true);
    }

    public function isParent(array $options, ...$classes): bool
    {
        if (null === $options['parent']) {
            return false;
        }

        return in_array($options['parent'], $classes, true);
    }
}
