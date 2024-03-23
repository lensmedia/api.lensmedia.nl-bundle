<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

trait RepositoryDecoratorTrait
{
    private readonly ServiceEntityRepositoryInterface $inner;

    public function __construct(#[AutowireDecorated] ServiceEntityRepositoryInterface $inner)
    {
        $this->inner = $inner;
    }

    /** @see ObjectRepository::find() */
    public function find($id): ?object
    {
        return $this->inner->find($id);
    }

    /** @see ObjectRepository::findAll() */
    public function findAll(): array
    {
        return $this->inner->findAll();
    }

    /** @see ObjectRepository::findBy() */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->inner->findBy($criteria, $orderBy, $limit, $offset);
    }

    /** @see ObjectRepository::findOneBy() */
    public function findOneBy(array $criteria): ?object
    {
        return $this->inner->findOneBy($criteria);
    }

    /** @see ObjectRepository::getClassName() */
    public function getClassName(): string
    {
        return $this->inner->getClassName();
    }

    // Pass any other class to the inner repository
    public function __call(string $name, array $arguments): mixed
    {
        return $this->inner->{$name}(...$arguments);
    }
}
