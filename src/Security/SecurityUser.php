<?php

namespace Lens\Bundle\LensApiBundle\Security;

use Lens\Bundle\LensApiBundle\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLES = [self::ROLE_ADMIN, self::ROLE_USER];
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    private function __construct(
        private readonly string $username,
        private readonly ?string $password = null,
        private readonly array $roles = [SecurityUser::ROLE_USER]
    ) {
    }

    public static function fromUser(User $user): SecurityUser
    {
        return new self(
            $user->username,
            $user->password ?? null,
            $user->roles
        );
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }
}
