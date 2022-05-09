<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\LensApi;

class AbstractRepository implements LensApiRepositoryInterface
{
    public function __construct(
        protected LensApi $api,
    ) {
    }
}
