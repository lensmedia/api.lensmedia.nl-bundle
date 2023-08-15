<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Collections\Selectable;
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
    public function find($id)
    {
        return $this->inner->find($id);
    }

    /** @see ObjectRepository::findAll() */
    public function findAll()
    {
        return $this->inner->findAll();
    }

    /** @see ObjectRepository::findBy() */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
    {
        return $this->inner->findBy($criteria, $orderBy, $limit, $offset);
    }

    /** @see ObjectRepository::findOneBy() */
    public function findOneBy(array $criteria)
    {
        return $this->inner->findOneBy($criteria);
    }

    /** @see ObjectRepository::getClassName() */
    public function getClassName()
    {
        return $this->inner->getClassName();
    }

    // Pass any other class to the inner repository
    public function __call(string $name, array $arguments)
    {
        return $this->inner->{$name}(...$arguments);
    }
}
