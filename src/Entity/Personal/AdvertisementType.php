<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity\Personal;

enum AdvertisementType: string
{
    case Email = 'email';
    case Mail = 'mail';

    public function isEmail(): bool
    {
        return self::Email === $this;
    }

    public function isMail(): bool
    {
        return self::Mail === $this;
    }
}
