<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\EmployeeRepository;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\ManyToOne(targetEntity: Personal::class, inversedBy: 'companies')]
    #[Assert\Valid]
    public Personal $personal;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'employees')]
    #[Assert\Valid]
    public Company $company;

    #[ORM\Column(name: '`function`', nullable: true)]
    public string $function;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    public array $roles = [];

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public function setPersonal(Personal $personal): void
    {
        if (!isset($this->personal) || ($this->personal !== $personal)) {
            if (!empty($this->personal)) {
                $this->personal->removeCompany($this);
            }

            $this->personal = $personal;
            $personal->addCompany($this);
        }
    }

    public function setCompany(Company $company): void
    {
        if (!isset($this->company) || ($this->company !== $company)) {
            if (!empty($this->company)) {
                $this->company->removeEmployee($this);
            }

            $this->company = $company;
            $company->addEmployee($this);
        }
    }
}
