<?php

namespace Lens\Bundle\LensApiBundle\Data;

use DateTimeImmutable;
use DateTimeInterface;
use Lens\Bundle\LensApiBundle\Validator as Validators;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[Validators\UniqueUser(message: 'user.unique_user')]
class User
{
    public const AUTH_NOT_FOUND = '6b4281f6-9bf3-4e67-9e31-cf31723ab714';
    public const AUTH_INVALID_PASSWORD = 'f85765a3-df36-40e8-b9f7-5e532ef5a9a0';

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLES = [self::ROLE_ADMIN, self::ROLE_USER];

    #[Assert\NotBlank(message: 'user.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'user.username.not_blank')]
    public string $username;

    #[Assert\Length(min: 3, minMessage: 'user.password.length.min')]
    public ?string $plainPassword = null;

    /**
     * @Assert\NotBlank(message="user.roles.not_blank")
     * @Assert\All({
     *     @Assert\Choice(choices=User::ROLES, message="user.roles.choice")
     * })
     */
    public array $roles = [self::ROLE_USER];

    public ?string $authToken = null;

    public ?string $recoveryToken = null;

    #[Assert\NotBlank(message: 'user.created_at.not_blank')]
    #[Assert\DateTime(message: 'user.created_at.datetime')]
    public DateTimeInterface $createdAt;

    #[Assert\NotBlank(message: 'user.updated_at.not_blank')]
    #[Assert\DateTime(message: 'user.updated_at.datetime')]
    public DateTimeInterface $updatedAt;

    #[Assert\DateTime(message: 'user.last_logged_in_at.datetime')]
    public ?DateTimeInterface $lastLoggedInAt = null;

    #[Assert\DateTime(message: 'user.disabled_at.datetime')]
    public ?DateTimeInterface $disabledAt = null;

    #[Assert\Valid]
    public ?Personal $personal = null;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->createdAt
            = $this->updatedAt
            = new DateTimeImmutable();
    }
}
