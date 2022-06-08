<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lens\Bundle\LensApiBundle\Form\Extension\ExclusionExtension;
use Lens\Bundle\LensApiBundle\Form\Type\AdvertisementChoiceType;
use Lens\Bundle\LensApiBundle\Form\Type\CompanyType;
use Lens\Bundle\LensApiBundle\Form\Type\DealerChoiceType;
use Lens\Bundle\LensApiBundle\Form\Type\DriversLicenceChoiceType;
use Lens\Bundle\LensApiBundle\LensApi;
use Lens\Bundle\LensApiBundle\Validator\UniqueAdvertisementValidator;
use Lens\Bundle\LensApiBundle\Validator\UniqueDealerValidator;
use Lens\Bundle\LensApiBundle\Validator\UniqueUserValidator;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(LensApi::class)
        ->args([
            service(SerializerInterface::class),
            service(HttpClientInterface::class),
            [],
        ])

        ->set(CompanyType::class)
        ->tag('form.type')
        ->args([
            service(LensApi::class),
        ])

        ->set(DealerChoiceType::class)
        ->tag('form.type')
        ->args([
            service(LensApi::class),
        ])

        ->set(DriversLicenceChoiceType::class)
        ->tag('form.type')
        ->args([
            service(LensApi::class),
        ])

        ->set(AdvertisementChoiceType::class)
        ->tag('form.type')
        ->args([
            service(LensApi::class),
        ])

        ->set(ExclusionExtension::class)
        ->args([false])
        ->tag('form.type_extension', ['priority' => -4096])

        ->set(UniqueAdvertisementValidator::class)
        ->args([
            service(LensApi::class),
        ])

        ->set(UniqueDealerValidator::class)
        ->args([
            service(LensApi::class),
        ])

        ->set(UniqueUserValidator::class)
        ->args([
            service(LensApi::class),
        ])
    ;
};
