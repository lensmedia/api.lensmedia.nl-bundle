<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\Auth;
use App\Controller\RecoveryController;
use App\Data\ResetPassword;
use App\DataFilters\Old\UserFilter;
use App\Entity\Personal\Personal;
use App\Security\SecurityUser;
use App\Serializer\AutoContextBuilder;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    collectionOperations: [
        'auth' => [
            'method' => 'GET',
            'path' => '/auth.{_format}',
            'security' => "is_granted('ROLE_USER') or is_granted('ROLE_ADMIN')",
            'controller' => Auth::class,
        ],
        self::SEARCH_OPERATION => [
            'method' => 'GET',
            'path' => '/users/search.{_format}',
            'openapi_context' => [
                'description' => 'Search for any user looking through multiple fields.',
                'parameters' => [
                    [
                        'name' => 'q',
                        'in' => 'query',
                        'description' => 'The search term(s) to look for.',
                        'type' => 'string',
                        'required' => true,
                    ],
                ],
            ],
        ],
        'get' => ['security' => "is_granted('ROLE_ADMIN')"],
        'post' => ['security' => "is_granted('ROLE_ADMIN')"],
    ],
    itemOperations: [
        'get' => ['security' => "is_granted('ROLE_ADMIN') or object.username == user.getUserIdentifier()"],
        'patch' => ['security' => "is_granted('ROLE_ADMIN') or object.username == user.getUserIdentifier()"],
        'delete' => ['security' => "is_granted('ROLE_ADMIN') or object.username == user.getUserIdentifier()"],
        'recovery-start' => [
            'method' => 'GET',
            'path' => '/users/{id}/recover.{_format}',
            'controller' => [RecoveryController::class, 'start'],
            'openapi_context' => [
                'summary' => 'Start password recovery process.',
            ],
        ],
        'recovery-check' => [
            'method' => 'GET',
            'path' => '/users/{id}/recover/{token}.{_format}',
            'controller' => [RecoveryController::class, 'check'],
            'status' => Response::HTTP_NO_CONTENT,
            'openapi_context' => [
                'summary' => 'Check if recovery is still possible.',
                'parameters' => [
                    [
                        'in' => 'path',
                        'name' => 'id',
                        'description' => 'User identifier',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                    [
                        'in' => 'path',
                        'name' => 'token',
                        'description' => 'Recovery token',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'responses' => [
                    Response::HTTP_NO_CONTENT => ['description' => 'Recovery is still possible.'],
                    Response::HTTP_BAD_REQUEST => ['description' => 'Invalid recovery token.'],
                    Response::HTTP_FORBIDDEN => ['description' => 'Recovery token has expired.'],
                    Response::HTTP_NOT_FOUND => ['description' => 'User not found.'],
                ],
            ],
        ],
        'recovery-finish' => [
            'method' => 'PATCH',
            'path' => '/users/{id}/recover/{token}.{_format}',
            'input' => ResetPassword::class,
            'denormalization_context' => [AutoContextBuilder::DISABLE => true],
            'openapi_context' => [
                'summary' => 'Finish recovery by updating the users password.',
                'parameters' => [
                    [
                        'in' => 'path',
                        'name' => 'id',
                        'description' => 'User identifier',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                    [
                        'in' => 'path',
                        'name' => 'token',
                        'description' => 'Recovery token',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'responses' => [
                    Response::HTTP_BAD_REQUEST => ['description' => 'Invalid recovery token.'],
                    Response::HTTP_FORBIDDEN => ['description' => 'Recovery token has expired.'],
                    Response::HTTP_NOT_FOUND => ['description' => 'User not found.'],
                ],
            ],
        ],
    ],
    denormalizationContext: [
        'groups' => ['user'],
    ],
    normalizationContext: [
        'groups' => ['user'],
    ],
)]
#[ApiFilter(UserFilter::class)]
#[ApiFilter(SearchFilter::class, properties: [
    'username' => 'exact',
])]
#[UniqueEntity(fields: ['username'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    public const AUTH_USER_NOT_FOUND = '6b4281f6-9bf3-4e67-9e31-cf31723ab714';
    public const AUTH_INVALID_PASSWORD = 'f85765a3-df36-40e8-b9f7-5e532ef5a9a0';
    public const AUTH_USER_BLOCKED = '80d61237-ce0f-441f-8379-95b7790e128a';

    public const RECOVERY_TIMEOUT = '+3 hours';

    public const SEARCH_OPERATION = 'search';

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
    public array $roles = [SecurityUser::ROLE_USER];

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
    #[ApiSubresource(maxDepth: 1)]
    public ?Personal $personal = null;

    public int $weight = 0;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->createdAt
            = $this->updatedAt
            = new DateTimeImmutable();
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
        $invalid = empty($this->authToken) || strlen($this->authToken) !== 64;
        if (!$invalid) {
            $ulid = Ulid::fromBinary(hex2bin(substr($this->authToken, 0, 32)));

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
        UserPasswordHasherInterface $userPasswordHasher,
        string $plainPassword,
    ): void {
        // Also generate a new auth token when password is reset.
        $this->auth();

        /* @todo remove plain password with legacy thing */
        $this->plainPassword = $plainPassword;
        $this->password = $userPasswordHasher->hashPassword(
            SecurityUser::fromUser($this),
            $plainPassword,
        );

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
