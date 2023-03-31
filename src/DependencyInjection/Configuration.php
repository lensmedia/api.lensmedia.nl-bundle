<?php

namespace Lens\Bundle\LensApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lens_lens_api');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->end();

        return $treeBuilder;
    }
}
