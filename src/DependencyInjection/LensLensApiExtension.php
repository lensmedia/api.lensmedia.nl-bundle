<?php

namespace Lens\Bundle\LensApiBundle\DependencyInjection;

use Lens\Bundle\LensApiBundle\SendInBlue\SendInBlue;
use RuntimeException;
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
        $config = $container->resolveEnvPlaceholders($config, true);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        if (!preg_match('/^(([a-z_]+:\d+),)*([a-z_]+:\d+)$/', $config['send_in_blue']['dealer_lists'])) {
            throw new RuntimeException('"lens_lens_api.send_in_blue.dealer_lists" configuration must match the format "dealer=1,other_dealer=2". The dealer name must match those from the Dealer entity records for it to work.');
        }

        $sendInBlue = $container->getDefinition(SendInBlue::class);
        $sendInBlue->replaceArgument(1, $config['send_in_blue']['api_key']);
        $sendInBlue->replaceArgument(2, $config['send_in_blue']['subscriber_list']);
        $sendInBlue->replaceArgument(3, $config['send_in_blue']['dealer_lists']);
    }
}
