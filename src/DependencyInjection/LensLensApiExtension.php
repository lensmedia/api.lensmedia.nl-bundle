<?php

namespace Lens\Bundle\LensApiBundle\DependencyInjection;

use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LensLensApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $container
            ->getDefinition(LensApi::class)
            ->replaceArgument(2, $config['http_client_options']);
    }
}
