<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Lens\Bundle\LensApiBundle\Doctrine\LensServiceEntityRepository;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;

class EmployeeRepository extends LensServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }
}
