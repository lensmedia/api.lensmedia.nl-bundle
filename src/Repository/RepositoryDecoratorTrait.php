<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\MapDecorated;

/**
 * @property ServiceEntityRepository $inner
 */
trait RepositoryDecoratorTrait
{
    public function __construct(
        #[MapDecorated]
        private readonly ServiceEntityRepositoryInterface $inner,
    ) {
    }

    public function __get(string $name)
    {
        return $this->inner->{$name};
    }

    public function __set(string $name, $value): void
    {
        $this->inner->{$name} = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->inner->{$name});
    }

    public function __call(string $name, array $arguments)
    {
        return $this->inner->{$name}(...$arguments);
    }
}
