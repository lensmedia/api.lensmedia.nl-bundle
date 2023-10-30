<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use LogicException;
use Symfony\Component\Uid\Ulid;

trait RecoveryTrait
{
    #[ORM\Column(unique: true, nullable: true)]
    public ?string $recoveryToken = null;

    /**
     * Starts the recovery process by generating a new token.
     */
    public function startRecovery(): void
    {
        $this->checkRecoveryInterface();

        $this->recoveryToken = (new Ulid())->toBase58();
    }

    /**
     * Returns the date and time when the recovery token expires or null if no recovery was initiated.
     */
    public function recoveryExpiresAt(): ?DateTimeImmutable
    {
        $this->checkRecoveryInterface();

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

        return $dateTimePart->add(self::recoveryTimeout());
    }

    /**
     * Checks if the user started a recovery process and if the token is still valid.
     */
    public function canRecoverAccount(?DateTimeInterface $timestamp = null): bool
    {
        $this->checkRecoveryInterface();

        if (!$this->recoveryExpiresAt()) {
            return false;
        }

        return ($timestamp ?? new DateTimeImmutable()) < $this->recoveryExpiresAt();
    }

    /**
     * Cleans up the recovery token and calls the callable with the user object.
     * This does not update the password, you need to modify the user object yourself.
     */
    public function finishRecovery(callable $callable): void
    {
        $this->checkRecoveryInterface();

        $callable($this);

        $this->recoveryToken = null;
    }

    private function checkRecoveryInterface(): void
    {
        if (is_a(self::class, RecoveryInterface::class, true)) {
            return;
        }

        throw new LogicException(sprintf(
            'Class "%s" must implement "%s" interface.',
            self::class,
            RecoveryInterface::class,
        ));
    }
}
