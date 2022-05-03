<?php

namespace Lens\Bundle\LensApiBundle\Data;

use DateTimeInterface;
use Symfony\Component\Uid\Ulid;

class User
{
    public const AUTH_NOT_FOUND = '6b4281f6-9bf3-4e67-9e31-cf31723ab714';
    public const AUTH_INVALID_PASSWORD = 'f85765a3-df36-40e8-b9f7-5e532ef5a9a0';

    public Ulid $id;

    public string $username;

    public array $roles = [];

    public ?string $authToken = null;

    public ?string $recoveryToken = null;

    public DateTimeInterface $createdAt;

    public DateTimeInterface $updatedAt;

    public ?DateTimeInterface $lastLoggedInAt = null;

    public ?DateTimeInterface $disabledAt = null;

    public ?DateTimeInterface $deletedAt = null;

    public ?Personal $personal = null;
}
