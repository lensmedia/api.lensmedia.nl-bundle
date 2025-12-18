<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

enum Role: string
{
    case Admin = 'ROLE_ADMIN';
    case User = 'ROLE_USER';
}
