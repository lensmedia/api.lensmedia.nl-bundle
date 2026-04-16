<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\asd;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;

trait IsLoadingFixturesInDebugTrait
{
    /**
     * Helper that returns true if running debug and loading fixtures. Useful for disabling doctrine listeners that
     * would trigger indexing during fixtures loading. If you want to have these synchronized create/run a command
     * for all (new) entries after completion.
     *
     * Might be worth checking into https://github.com/zenstruck/foundry/pull/216/changes or something more recent
     * if this gets out of hand in use cases. It might be better to have some service that can toggle this instead
     * of relying on backtrace check for fixtures command.
     */
    protected function isLoadingFixturesInDebug(?bool $debug = null): bool
    {
        $debug ??= $this->isDebug ?? false;

        // No need to check any further.
        if (!class_exists(LoadDataFixturesDoctrineCommand::class)) {
            return false;
        }

        static $disable = $debug && array_any(
            debug_backtrace(),
            static fn (array $call) => isset($call['class']) && is_a($call['class'], LoadDataFixturesDoctrineCommand::class, true),
        );

        return $disable;
    }
}
