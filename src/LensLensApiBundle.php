<?php

namespace Lens\Bundle\LensApiBundle;

use Lens\Bundle\LensApiBundle\Brevo\Brevo;
use Lens\Bundle\LensApiBundle\DependencyInjection\Compiler\MeilisearchCompilerPass;
use Lens\Bundle\LensApiBundle\Doctrine\Event\UpdateBrevoListener;
use Lens\Bundle\LensApiBundle\Meilisearch\CompanySearch;
use Lens\Bundle\MeilisearchBundle\LensMeilisearch;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class LensLensApiBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new MeilisearchCompilerPass());
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/packages/doctrine.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $config = $builder->resolveEnvPlaceholders($config, true);

        // Brevo services toggle
        $builder->setParameter('lens_lens_api.brevo.api_key', $brevoApiKey = $config['brevo']['api_key'] ?? null);
        $builder->setParameter('lens_lens_api.brevo.subscriber_list', $brevoSubscriberList = $config['brevo']['subscriber_list'] ?? null);
        if (empty($brevoApiKey) || empty($brevoSubscriberList)) {
            $builder->removeDefinition(Brevo::class);
            $builder->removeDefinition(UpdateBrevoListener::class);
        }

        // Meilisearch services toggle
        $builder->setParameter('lens_lens_api.meilisearch.url', $meilisearchUrl = $config['meilisearch']['url'] ?? null);
        $builder->setParameter('lens_lens_api.meilisearch.key', $meilisearchKey = $config['meilisearch']['key'] ?? null);
        if (empty($meilisearchUrl) || empty($meilisearchKey) || !ContainerBuilder::willBeAvailable('lensmedia/symfony-meilisearch', LensMeilisearch::class, ['symfony/framework-bundle'])) {
            $builder->removeDefinition(CompanySearch::class);
        }
    }
}
