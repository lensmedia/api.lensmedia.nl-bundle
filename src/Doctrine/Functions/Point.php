<?php

namespace Lens\Bundle\LensApiBundle\Doctrine\Functions;

class Point extends AbstractSpatialDQLFunction
{
    protected array $platforms = ['mysql'];

    protected string $functionName = 'Point';

    protected int $minGeomExpr = 2;

    protected int $maxGeomExpr = 2;
}
