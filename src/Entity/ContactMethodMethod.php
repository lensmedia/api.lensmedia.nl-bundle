<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

enum ContactMethodMethod: string
{
    case Undefined = 'undefined';
    case Phone = 'phone';
    case Email = 'email';
    case Website = 'website';
    case Social = 'social';

    public function isUndefined(): bool
    {
        return self::Undefined === $this;
    }

    public function isPhone(): bool
    {
        return self::Phone === $this;
    }

    public function isEmail(): bool
    {
        return self::Email === $this;
    }

    public function isWebsite(): bool
    {
        return self::Website === $this;
    }

    public function isSocial(): bool
    {
        return self::Social === $this;
    }
}
