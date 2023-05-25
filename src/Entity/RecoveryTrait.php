<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use LogicException;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Uid\Ulid;

trait RecoveryTrait
{
    #[ORM\Column(unique: true, nullable: true)]
    public ?string $recoveryToken = null;

    public function startRecovery(): void
    {
        $this->recoveryToken = (new Ulid())->toBase58();
    }

    public function recoveryExpiresAt(): ?DateTimeImmutable
    {
        if (!$this->recoveryToken) {
            return null;
        }

        try {
            $dateTimePart = Ulid::fromBase58($this->recoveryToken)->getDateTime();
        } catch (Exception) {
            throw new LogicException(sprintf(
                'Invalid recovery token "%s".',
                $this->recoveryToken,
            ));
        }

        if (!method_exists(self::class, 'recoveryTimeout')) {
            throw new LogicException(sprintf(
                'Class "%s" must implement "%s::recoveryTimeout()" method.',
                self::class,
                RecoveryInterface::class,
            ));
        }

        return $dateTimePart->add(self::recoveryTimeout());
    }

    public function canRecoverAccount(?DateTimeInterface $timestamp = null): bool
    {
        if (!$this->recoveryExpiresAt()) {
            return false;
        }

        return ($timestamp ?? new DateTimeImmutable()) < $this->recoveryExpiresAt();
    }

    public function finishRecovery(PasswordHasherInterface $passwordHasher, string $plainPassword): void
    {
        $this->updatePassword($passwordHasher, $plainPassword);
        $this->recoveryToken = null;
    }
}
