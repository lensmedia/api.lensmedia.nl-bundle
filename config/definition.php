<?php

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->arrayNode('brevo')
                ->children()
                    ->scalarNode('api_key')->isRequired()->end()
                    ->scalarNode('subscriber_list')->isRequired()->end()
                    ->scalarNode('dealer_lists')->isRequired()->end()
                ->end()
            ->end()

            ->arrayNode('meili_search')
                ->children()
                    ->scalarNode('url')->isRequired()->end()
                    ->scalarNode('key')->isRequired()->end()
                ->end()
            ->end()
        ->end()
    ;
};
