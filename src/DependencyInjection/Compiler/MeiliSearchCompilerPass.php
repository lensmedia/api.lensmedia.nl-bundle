<?php

namespace Lens\Bundle\LensApiBundle\DependencyInjection\Compiler;

use Lens\Bundle\MeiliSearchBundle\LensMeiliSearch;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add lens_api client to LensMeiliSearch if it is available.
 */
final readonly class MeiliSearchCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('lens_lens_api.meili_search.url') || !$container->getParameter('lens_lens_api.meili_search.key') || !$container->hasDefinition(LensMeiliSearch::class)) {
            return;
        }

        $arguments = $container->resolveEnvPlaceholders([
            'lens_api',
            $container->getParameter('lens_lens_api.meili_search.url'),
            $container->getParameter('lens_lens_api.meili_search.key'),
        ], true);

        $definition = $container->getDefinition(LensMeiliSearch::class);
        $definition->addMethodCall('addClient', $arguments);

        // Reorder the method calls so that the addClient method is called first.
        $methodCalls = $definition->getMethodCalls();
        $definition->setMethodCalls(array_merge([array_pop($methodCalls)], $methodCalls));
    }
}
