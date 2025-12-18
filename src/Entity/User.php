<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(fields: ['username'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements RecoveryInterface
{
    use RecoveryTrait;

    public const string RECOVERY_TIMEOUT = 'PT3H';

    /** @deprecated use Role::Admin enum instead */
    public const string ROLE_ADMIN = 'ROLE_ADMIN';

    /** @deprecated use Role::User enum instead */
    public const string ROLE_USER = 'ROLE_USER';

    public const array ROLES = [
        Role::Admin->value => Role::Admin->value,
        Role::User->value => Role::User->value,
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
    public array $roles = [Role::User->value];

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

    #[ORM\OneToOne(targetEntity: Personal::class, mappedBy: 'user', cascade: ['persist', 'refresh', 'detach'])]
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
        $this->personal = $personal;
        $personal?->setUser($this);
    }

    public function updatePassword(
        PasswordHasherInterface $passwordHasher,
        string $plainPassword,
    ): void {
        /* @todo remove plain password with legacy thing */
        $this->plainPassword = $plainPassword;
        $this->password = $passwordHasher->hash($plainPassword);
    }

    public function disable(): void
    {
        $this->disabledAt = new DateTimeImmutable();
    }

    public function enable(): void
    {
        $this->disabledAt = null;
    }

    public function isDisabled(): bool
    {
        return null !== $this->disabledAt;
    }

    public static function recoveryTimeout(): DateInterval
    {
        return new DateInterval(self::RECOVERY_TIMEOUT);
    }
}
