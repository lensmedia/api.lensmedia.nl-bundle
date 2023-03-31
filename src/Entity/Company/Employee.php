<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: ['get', 'patch', 'delete'],
    subresourceOperations: [
        'api_companies_employees_get_subresource' => [
            'normalization_context' => [
                'groups' => ['company'],
            ],
        ],
        'api_driving_schools_employees_get_subresource' => [
            'normalization_context' => [
                'groups' => ['driving_school'],
            ],
        ],
        'api_personals_employees_get_subresource' => [
            'normalization_context' => [
                'groups' => ['personal'],
            ],
        ],
    ],
)]
#[ORM\Entity]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\ManyToOne(targetEntity: Personal::class, inversedBy: 'companies')]
    #[ApiSubresource(maxDepth: 1)]
    #[Assert\Valid]
    public Personal $personal;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'employees')]
    #[Assert\Valid]
    public Company $company;

    #[ORM\Column(name: '`function`', nullable: true)]
    public string $function;

    #[ORM\Column(type: 'simple_array')]
    public array $roles = [];

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public function setPersonal(Personal $personal): void
    {
        if (!isset($this->personal) || (isset($this->personal) && $this->personal !== $personal)) {
            if (!empty($this->personal)) {
                $this->personal->removeCompany($this);
            }

            $personal->addCompany($this);
            $this->personal = $personal;
        }
    }

    public function setCompany(Company $company): void
    {
        if (!isset($this->company) || (isset($this->company) && $this->company !== $company)) {
            if (!empty($this->company)) {
                $this->company->removeEmployee($this);
            }

            $company->addEmployee($this);
            $this->company = $company;
        }
    }
}
