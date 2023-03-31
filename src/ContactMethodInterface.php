<?php

namespace Lens\Bundle\LensApiBundle;

interface ContactMethodInterface
{
    public const METHODS = [
        self::UNDEFINED,
        self::PHONE,
        self::EMAIL,
        self::WEBSITE,
        self::SOCIAL,
        self::CUSTOM,
    ];

    public const UNDEFINED = 'undefined';
    public const PHONE = 'phone';
    public const EMAIL = 'email';
    public const WEBSITE = 'website';
    public const SOCIAL = 'social';
    public const CUSTOM = 'custom';
}
