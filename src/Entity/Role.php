<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

enum Role: string
{
    case Admin = 'ROLE_ADMIN';
    case User = 'ROLE_USER';

    public function isAdmin(): bool
    {
        return self::Admin === $this;
    }

    public function isUser(): bool
    {
        return self::User === $this;
    }
}
