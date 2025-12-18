<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Doctrine\Functions;

class STDistanceSphere extends AbstractSpatialDQLFunction
{
    protected array $platforms = ['mysql'];

    protected string $functionName = 'ST_Distance_SPHERE';

    protected int $minGeomExpr = 2;

    protected int $maxGeomExpr = 2;
}
