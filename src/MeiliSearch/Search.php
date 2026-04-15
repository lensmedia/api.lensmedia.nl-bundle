<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\MeiliSearch;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearch;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearchDocumentLoaderInterface;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearchIndexLoaderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract readonly class Search implements LensMeiliSearchDocumentLoaderInterface, LensMeiliSearchIndexLoaderInterface
{
    use IsLoadingFixturesInDebugTrait;

    public function __construct(
        protected LensMeiliSearch $lensMeiliSearch,
        #[Autowire(param: 'kernel.debug')]
        protected bool $isDebug,
    ) {
    }

    abstract public function onUpdate(object $object, LifecycleEventArgs $event): void;

    abstract public function onRemove(object $object, LifecycleEventArgs $event): void;
}
