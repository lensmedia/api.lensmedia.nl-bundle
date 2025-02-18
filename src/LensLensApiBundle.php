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

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        // Brevo services toggle
        if (empty($config['brevo'])) {
            $builder->removeDefinition(Brevo::class);
            $builder->removeDefinition(UpdateBrevoListener::class);
        } else {
            $brevo = $builder->getDefinition(Brevo::class);
            $brevo->replaceArgument(1, $config['brevo']['api_key']);
            $brevo->replaceArgument(2, $config['brevo']['subscriber_list']);
            $brevo->replaceArgument(3, $config['brevo']['dealer_lists']);
        }

        // MeiliSearch services toggle
        if (ContainerBuilder::willBeAvailable('lensmedia/symfony-meili-search', LensMeiliSearch::class, ['lensmedia/api.lensmedia.nl-bundle'])) {
            $builder->setParameter('lens_lens_api.meili_search.url', $config['meili_search']['url'] ?? null);
            $builder->setParameter('lens_lens_api.meili_search.key', $config['meili_search']['key'] ?? null);
        } else {
            $builder->removeDefinition(CompanySearch::class);
            $builder->removeDefinition(UpdateMeiliSearchListener::class);
        }
    }

    public function process(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MeiliSearchCompilerPass());
    }
}
