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
}
