<?php

namespace Lens\Bundle\LensApiBundle;

use Lens\Bundle\LensApiBundle\Brevo\Brevo;
use Lens\Bundle\LensApiBundle\DependencyInjection\Compiler\MeiliSearchCompilerPass;
use Lens\Bundle\LensApiBundle\Doctrine\Event\UpdateBrevoListener;
use Lens\Bundle\LensApiBundle\Doctrine\Event\UpdateMeiliSearchListener;
use Lens\Bundle\LensApiBundle\MeiliSearch\CompanySearch;
use Lens\Bundle\MeiliSearchBundle\LensMeiliSearch;
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

        $container->addCompilerPass(new MeiliSearchCompilerPass());
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
        $builder->setParameter('lens_lens_api.brevo.dealer_lists', $brevoDealerLists = $config['brevo']['dealer_lists'] ?? null);
        if (empty($brevoApiKey) || empty($brevoSubscriberList) || empty($brevoDealerLists)) {
            $builder->removeDefinition(Brevo::class);
            $builder->removeDefinition(UpdateBrevoListener::class);
        }

        // MeiliSearch services toggle
        $builder->setParameter('lens_lens_api.meili_search.url', $meiliSearchUrl = $config['meili_search']['url'] ?? null);
        $builder->setParameter('lens_lens_api.meili_search.key', $meiliSearchKey = $config['meili_search']['key'] ?? null);
        if (empty($meiliSearchUrl) || empty($meiliSearchKey) || !ContainerBuilder::willBeAvailable('lensmedia/symfony-meili-search', LensMeiliSearch::class, ['symfony/framework-bundle'])) {
            $builder->removeDefinition(CompanySearch::class);
            $builder->removeDefinition(UpdateMeiliSearchListener::class);
        }
    }
}
