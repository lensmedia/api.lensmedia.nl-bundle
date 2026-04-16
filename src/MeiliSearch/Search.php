<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Meilisearch;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Lens\Bundle\MeilisearchBundle\LensMeilisearch;
use Lens\Bundle\MeilisearchBundle\LensMeilisearchDocumentLoaderInterface;
use Lens\Bundle\MeilisearchBundle\LensMeilisearchIndexLoaderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract readonly class Search implements LensMeilisearchDocumentLoaderInterface, LensMeilisearchIndexLoaderInterface
{
    use IsLoadingFixturesInDebugTrait;

    public function __construct(
        protected LensMeilisearch $lensMeilisearch,
        #[Autowire(param: 'kernel.debug')]
        protected bool $isDebug,
    ) {
    }

    abstract public function onUpdate(object $object, LifecycleEventArgs $event): void;

    abstract public function onRemove(object $object, LifecycleEventArgs $event): void;
}
