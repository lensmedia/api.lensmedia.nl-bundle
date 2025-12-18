<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

enum RemarkLevel: string
{
    case Default = 'default';
    case Info = 'info';
    case Question = 'question';
    case Important = 'important';
    case Warning = 'warning';
    case Danger = 'danger';
}
