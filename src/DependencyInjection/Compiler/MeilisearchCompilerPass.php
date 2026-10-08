<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\DependencyInjection\Compiler;

use Lens\Bundle\LensApiBundle\Meilisearch\CompanySearch;
use Lens\Bundle\LensApiBundle\Meilisearch\PersonalSearch;
use Lens\Bundle\LensApiBundle\Meilisearch\UserPersonalSearch;
use Lens\Bundle\LensApiBundle\Meilisearch\UserSearch;
use Lens\Bundle\MeilisearchBundle\LensMeilisearch;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add lens_api client to LensMeilisearch if it is available.
 */
final readonly class MeilisearchCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('lens_lens_api.meilisearch.url') || !$container->getParameter('lens_lens_api.meilisearch.key') || !$container->hasDefinition(LensMeilisearch::class)) {
            self::removeSearchDefinitions($container);

            return;
        }

        $arguments = $container->resolveEnvPlaceholders([
            'lens_api',
            $container->getParameter('lens_lens_api.meilisearch.url'),
            $container->getParameter('lens_lens_api.meilisearch.key'),
        ], true);

        $definition = $container->getDefinition(LensMeilisearch::class);
        $definition->addMethodCall('addClient', $arguments);

        // Reorder the method calls so that the addClient method is called first.
        $methodCalls = $definition->getMethodCalls();
        $definition->setMethodCalls(array_merge([array_pop($methodCalls)], $methodCalls));
    }

    public static function removeSearchDefinitions(ContainerBuilder $container): void
    {
        $container->removeDefinition(CompanySearch::class);
        $container->removeDefinition(PersonalSearch::class);
        $container->removeDefinition(UserPersonalSearch::class);
        $container->removeDefinition(UserSearch::class);
    }
}
