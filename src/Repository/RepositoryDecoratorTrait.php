<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\Persistence\ObjectRepository;

trait RepositoryDecoratorTrait
{
    /** @see ObjectRepository::find() */
    public function find(mixed $id, LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?object
    {
        return $this->inner->find($id, $lockMode, $lockVersion);
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
    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        return $this->inner->findOneBy($criteria, $orderBy);
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
