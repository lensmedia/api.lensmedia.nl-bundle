<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use DateInterval;
use DateTimeImmutable;

interface RecoveryInterface
{
    public static function recoveryTimeout(): DateInterval;

    public function startRecovery(): void;

    public function recoveryExpiresAt(): ?DateTimeImmutable;

    public function canRecoverAccount(): bool;

    public function finishRecovery(callable $callable): void;
}
