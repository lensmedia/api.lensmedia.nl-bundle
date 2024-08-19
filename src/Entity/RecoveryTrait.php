<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use LogicException;
use Symfony\Component\Uid\Ulid;

use function sprintf;

trait RecoveryTrait
{
    #[ORM\Column(unique: true, nullable: true)]
    public ?string $recoveryToken = null;

    public function startRecovery(): void
    {
        $this->checkRecoveryInterface();

        $this->recoveryToken = (new Ulid())->toBase58();
    }

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

    public function canRecoverAccount(?DateTimeInterface $timestamp = null): bool
    {
        $this->checkRecoveryInterface();

        if (!$this->recoveryExpiresAt()) {
            return false;
        }

        return ($timestamp ?? new DateTimeImmutable()) < $this->recoveryExpiresAt();
    }

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
