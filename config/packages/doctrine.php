<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('doctrine', [
        'dbal' => [
            'connections' => [
                'lens_api' => [
                    'url' => env('LENS_API_DATABASE_URL'),
                ],
            ],
        ],
        'orm' => [
            'entity_managers' => [
                'lens_api' => [
                    'naming_strategy' => \Lens\Bundle\LensApiBundle\Doctrine\NamespacedUnderscoreNamingStrategy::class,
                    'connection' => 'lens_api',
                    'mappings' => [
                        'Lens\Bundle\LensApiBundle' => [
                            'is_bundle' => false,
                            'type' => 'attribute',
                            'dir' => param('kernel.project_dir').'/vendor/lensmedia/api.lensmedia.nl-bundle/src/Entity',
                            'prefix' => 'Lens\Bundle\LensApiBundle\Entity',
                        ],
                    ],

                    'dql' => [
                        'string_functions' => [
                            'field' => \DoctrineExtensions\Query\Mysql\Field::class,
                            'hex' => \DoctrineExtensions\Query\Mysql\Hex::class,
                        ],
                        'numeric_functions' => [
                            'acos' => \DoctrineExtensions\Query\Mysql\Acos::class,
                            'cos' => \DoctrineExtensions\Query\Mysql\Cos::class,
                            'radians' => \DoctrineExtensions\Query\Mysql\Radians::class,
                            'sin' => \DoctrineExtensions\Query\Mysql\Sin::class,
                            'point' => \Lens\Bundle\LensApiBundle\Doctrine\Functions\Point::class,
                            'st_distance_sphere' => \Lens\Bundle\LensApiBundle\Doctrine\Functions\STDistanceSphere::class,
                        ],
                        'datetime_functions' => [
                            'date' => \DoctrineExtensions\Query\Mysql\Date::class,
                        ],
                    ],
                ],
            ],
        ]
    ]);
};
