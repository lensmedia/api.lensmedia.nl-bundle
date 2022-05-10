<?php

namespace Lens\Bundle\LensApiBundle\Data;

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

    public Ulid $id; // 01FWGBYM1VGJ9NDC5WG2SDDT56

    public string $method; // phone

    public string $value; // +31 529 484 655

    public ?string $label = null;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
