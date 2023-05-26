<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use DateInterval;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

interface RecoveryInterface
{
    public static function recoveryTimeout(): DateInterval;

    public function startRecovery(): void;

    public function recoveryExpiresAt(): ?DateTimeImmutable;

    public function canRecoverAccount(): bool;

    public function finishRecovery(PasswordHasherInterface $passwordHasher, string $plainPassword): void;

    public function updatePassword(PasswordHasherInterface $passwordHasher, string $plainPassword): void;
}
