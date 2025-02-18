<?php

declare(strict_types=1);

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrine) {
    $doctrine->dbal()->connection('lens_api')->url('%env(resolve:LENS_API_DATABASE_URL)%');

    $em = $doctrine->orm()->entityManager('lens_api');

    $em->namingStrategy(\Lens\Bundle\LensApiBundle\Doctrine\NamespacedUnderscoreNamingStrategy::class)
        ->connection('lens_api');

    $em->mapping('Lens\Bundle\LensApiBundle')
        ->isBundle(false)
        ->type('attribute')
        ->dir(dirname(__DIR__, 2).'/src/Entity')
        ->prefix('Lens\Bundle\LensApiBundle\Entity');

    $em->dql()
        ->stringFunction('field', \DoctrineExtensions\Query\Mysql\Field::class)
        ->stringFunction('hex', \DoctrineExtensions\Query\Mysql\Hex::class)

        ->numericFunction('acos', \DoctrineExtensions\Query\Mysql\Acos::class)
        ->numericFunction('cos', \DoctrineExtensions\Query\Mysql\Cos::class)
        ->numericFunction('radians', \DoctrineExtensions\Query\Mysql\Radians::class)
        ->numericFunction('sin', \DoctrineExtensions\Query\Mysql\Sin::class)
        ->numericFunction('point', \Lens\Bundle\LensApiBundle\Doctrine\Functions\Point::class)
        ->numericFunction('st_distance_sphere', \Lens\Bundle\LensApiBundle\Doctrine\Functions\STDistanceSphere::class)

        ->datetimeFunction('date', \DoctrineExtensions\Query\Mysql\Date::class)
    ;
};
