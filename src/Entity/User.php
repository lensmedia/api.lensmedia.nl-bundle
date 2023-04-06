<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\UserRepository;
use Lens\Bundle\LensApiBundle\Security\SecurityUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[UniqueEntity(fields: ['username'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    public const AUTH_USER_NOT_FOUND = '6b4281f6-9bf3-4e67-9e31-cf31723ab714';
    public const AUTH_INVALID_PASSWORD = 'f85765a3-df36-40e8-b9f7-5e532ef5a9a0';
    public const AUTH_USER_BLOCKED = '80d61237-ce0f-441f-8379-95b7790e128a';

    public const RECOVERY_TIMEOUT = '+3 hours';

    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_USER = 'ROLE_USER';

    public const ROLES = [
        self::ROLE_ADMIN => self::ROLE_ADMIN,
        self::ROLE_USER => self::ROLE_USER,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1)]
    #[ORM\Column(unique: true)]
    public string $username;

    #[ORM\Column]
    public string $password;

    #[ORM\Column]
    public string $plainPassword;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'simple_array')]
    public array $roles = [];

    #[ORM\Column(unique: true, nullable: true)]
    public ?string $authToken = null;

    #[ORM\Column(unique: true, nullable: true)]
    public ?string $recoveryToken = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeInterface $createdAt;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeInterface $lastLoggedInAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?DateTimeInterface $disabledAt = null;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Personal::class, cascade: ['persist'])]
    public ?Personal $personal = null;

    public int $weight = 0;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->createdAt
            = $this->updatedAt
            = new DateTimeImmutable();
    }

    public function displayName(): string
    {
        return $this->personal?->displayName() ?? $this->username;
    }

    public function setPersonal(?Personal $personal): void
    {
        if ($this->personal === $personal) {
            return;
        }

        $this->personal?->setUser(null);
        $personal?->setUser($this);
        $this->personal = $personal;
    }

    #[Assert\Callback]
    public function validateRoles(ExecutionContextInterface $context, $payload): void
    {
        if (empty(array_intersect($this->roles, SecurityUser::ROLES))) {
            $context->buildViolation('User has invalid role value.');
        }
    }

    public function auth(): string
    {
        $invalid = empty($this->authToken) || 64 !== mb_strlen($this->authToken);
        if (!$invalid) {
            $ulid = Ulid::fromBinary(hex2bin(mb_substr($this->authToken, 0, 32)));

            if (new DateTimeImmutable('-1 month') > $ulid->getDateTime()) {
                $invalid = true;
            }
        }

        if ($invalid) {
            $ulid = (new Ulid())->toBinary();

            $this->authToken = bin2hex($ulid).bin2hex(random_bytes(8));
        }

        $this->lastLoggedInAt = new DateTimeImmutable();

        return $this->authToken;
    }

    public function startRecovery(): void
    {
        $this->recoveryToken = (new Ulid())->toBase58();
    }

    public function recoveryExpiresAt(): ?DateTimeImmutable
    {
        return $this->recoveryToken
            ? Ulid::fromBase58($this->recoveryToken)
                ?->getDateTime()->modify(self::RECOVERY_TIMEOUT)
            : null;
    }

    public function canRecoverAccount(): bool
    {
        if (!$this->recoveryToken) {
            return false;
        }

        return new DateTimeImmutable() < $this->recoveryExpiresAt();
    }

    public function finishRecovery(
        PasswordHasherInterface $passwordHasher,
        string $plainPassword,
    ): void {
        // Also generate a new auth token when password is reset.
        $this->auth();

        /* @todo remove plain password with legacy thing */
        $this->plainPassword = $plainPassword;
        $this->password = $passwordHasher->hash($plainPassword);

        $this->recoveryToken = null;
    }

    public function disable(): void
    {
        $this->disabledAt = new DateTimeImmutable();
    }

    public function isDisabled(): bool
    {
        return null !== $this->disabledAt;
    }

    public function enable(): void
    {
        $this->disabledAt = null;
    }
}
