<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use DateInterval;
use DateTimeImmutable;

interface RecoveryInterface
{
    /**
     * Static method that defines how long recovery tokens are valid.
     */
    public static function recoveryTimeout(): DateInterval;

    /**
     * Starts the recovery process by generating a new token.
     */
    public function startRecovery(): void;

    /**
     * Returns the date and time when the recovery token expires or null if no recovery was initiated.
     */
    public function recoveryExpiresAt(): ?DateTimeImmutable;

    /**
     * Checks if the user started a recovery process and if the token is still valid.
     */
    public function canRecoverAccount(): bool;

    /**
     * Cleans up the recovery token and calls the callable with the user object.
     * This does not update the password, you need to modify the user object yourself.
     */
    public function finishRecovery(callable $callable): void;
}
