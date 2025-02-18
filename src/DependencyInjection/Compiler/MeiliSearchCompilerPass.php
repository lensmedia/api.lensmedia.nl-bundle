<?php

namespace Lens\Bundle\LensApiBundle\DependencyInjection\Compiler;

use Lens\Bundle\MeiliSearchBundle\LensMeiliSearch;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class MeiliSearchCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $url = $container->getParameter('lens_lens_api.meili_search.url');
        $key = $container->getParameter('lens_lens_api.meili_search.key');

        if (empty($url) || empty($key) || !$container->hasDefinition(LensMeiliSearch::class)) {
            return;
        }

        $definition = $container->getDefinition(LensMeiliSearch::class);
        $definition->addMethodCall('addClient', [
            'lens_api',
            $url,
            $key,
        ]);
    }
}
