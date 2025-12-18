<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\DealerRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: DealerRepository::class)]
#[ORM\Index(columns: ['name'])]
class Dealer
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column(unique: true)]
    public string $name;

    /** @var Collection<int, Company> */
    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'dealers')]
    public Collection $companies;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->companies = new ArrayCollection();
    }

    public function addCompany(Company $company): void
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->addDealer($this);
        }
    }

    public function removeCompany(Company $company): void
    {
        if ($this->companies->contains($company)) {
            $this->companies->removeElement($company);
            $company->removeDealer($this);
        }
    }
}
