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

    public function isDefault(): bool
    {
        return self::Default === $this;
    }

    public function isInfo(): bool
    {
        return self::Info === $this;
    }

    public function isQuestion(): bool
    {
        return self::Question === $this;
    }

    public function isImportant(): bool
    {
        return self::Important === $this;
    }

    public function isWarning(): bool
    {
        return self::Warning === $this;
    }

    public function isDanger(): bool
    {
        return self::Danger === $this;
    }
}
