<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Ulid;

class ContactMethod
{
    public const UNDEFINED = 'undefined';
    public const PHONE = 'phone';
    public const EMAIL = 'email';
    public const WEBSITE = 'website';
    public const PERSON = 'person';
    public const SOCIAL = 'social';
    public const CUSTOM = 'custom';
    public const METHODS = [
        self::UNDEFINED,
        self::PHONE,
        self::EMAIL,
        self::WEBSITE,
        self::PERSON,
        self::SOCIAL,
        self::CUSTOM,
    ];

    #[Assert\NotBlank(message: 'contact_method.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'contact_method.type.not_blank')]
    #[Assert\Choice(choices: self::METHODS, message: 'contact_method.type.choice')]
    public string $method = self::UNDEFINED;

    #[Assert\NotBlank(message: 'contact_method.value.not_blank')]
    public string $value;

    public ?string $label = null;
}
