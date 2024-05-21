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

        $this->addBrevoSection($rootNode);

        return $treeBuilder;
    }

    private function addBrevoSection($rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('brevo')
                    ->children()
                        ->scalarNode('api_key')->isRequired()->end()
                        ->scalarNode('subscriber_list')->isRequired()->end()
                        ->scalarNode('dealer_lists')->isRequired()->end()
                    ->end()
                ->end()
            ->end();
    }
}
