<?php

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ObjectRepository;

interface RepositoryDecoratorInterface extends ObjectRepository, ServiceEntityRepositoryInterface
{
}
