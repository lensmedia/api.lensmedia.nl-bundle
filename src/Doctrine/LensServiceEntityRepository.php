<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

use function sprintf;

abstract class LensServiceEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, ?string $class = null)
    {
        if (!$class || !class_exists($class)) {
            throw new LogicException(sprintf('Invalid or missing "class" parameter.
                %s must have it\'s own constructor, for example:
                public function __construct(ManagerRegistry $registry)
                {
                    parent::__construct($registry, YourEntity::class);
                }', static::class));
        }

        parent::__construct($registry, $class);
    }

    public function manager(): EntityManagerInterface
    {
        return $this->getEntityManager();
    }

    public function reference(string $class, mixed $id): ?object
    {
        return $this->manager()->getReference($class, $id);
    }

    public function add(object $object, bool $flush = false, bool $refresh = false): void
    {
        $this->manager()->persist($object);

        if ($flush) {
            $this->flush();
        }

        if ($refresh) {
            $this->refresh($object);
        }
    }

    public function save(object $object, bool $flush = false, bool $refresh = false): void
    {
        $this->add($object, $flush, $refresh);
    }

    public function contains(object $object): bool
    {
        return $this->manager()->contains($object);
    }

    public function remove(object $object, bool $flush = false): void
    {
        $this->manager()->remove($object);

        if ($flush) {
            $this->flush();
        }
    }

    public function refresh(object $object, LockMode|int|null $lockMode = null): void
    {
        $this->manager()->refresh($object, $lockMode);
    }

    public function flush(): void
    {
        $this->manager()->flush();
    }
}
