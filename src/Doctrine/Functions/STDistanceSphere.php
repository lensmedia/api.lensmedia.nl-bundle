<?php

namespace Lens\Bundle\LensApiBundle\Doctrine\Functions;

use CrEOF\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

class STDistanceSphere extends AbstractSpatialDQLFunction
{
    protected $platforms = ['mysql'];

    protected $functionName = 'ST_Distance_SPHERE';

    protected $minGeomExpr = 2;

    protected $maxGeomExpr = 2;
}
