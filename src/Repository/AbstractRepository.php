<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Lens\Bundle\LensApiBundle\LensApi;

abstract class AbstractRepository implements LensApiRepositoryInterface
{
    public function __construct(
        protected LensApi $api,
    ) {
    }

    abstract public function list(array $options = []): array;
}
