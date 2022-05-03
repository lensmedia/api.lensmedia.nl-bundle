<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(LensApi::class)
        ->args([
            service(SerializerInterface::class),
            service(HttpClientInterface::class),
            [],
        ]);
};
