<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Repository\DrivingSchoolRepository;

#[ORM\Entity(repositoryClass: DrivingSchoolRepository::class)]
#[ORM\Index(fields: ['cbr'])]
class DrivingSchool
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\OneToOne(targetEntity: Company::class, inversedBy: 'drivingSchool', cascade: ['persist', 'refresh', 'detach'])]
    #[ORM\JoinColumn(name: 'id', nullable: false)]
    public Company $company;

    #[ORM\Column(length: 32, nullable: true)]
    public ?string $cbr = null;

    /**
     * List of all drivers licences for a driving school that it gives lessons in.
     *
     * @var Collection<int, DriversLicence>
     */
    #[ORM\ManyToMany(targetEntity: DriversLicence::class, inversedBy: 'drivingSchools')]
    public Collection $driversLicences;

    public function __construct()
    {
        $this->driversLicences = new ArrayCollection();
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
        if ($company->getDrivingSchool() !== $this) {
            $company->setDrivingSchool($this);
        }
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function instructsForLicence(string $name): bool
    {
        return $this->driversLicences->exists(
            static fn (int $index, DriversLicence $drivingLicence) => strcasecmp($name, $drivingLicence->label),
        );
    }

    public function addDriversLicence(DriversLicence $driversLicence): void
    {
        if (!$this->driversLicences->contains($driversLicence)) {
            $this->driversLicences->add($driversLicence);
            $driversLicence->addDrivingSchool($this);
        }
    }

    public function removeDriversLicence(DriversLicence $driversLicence): void
    {
        if ($this->driversLicences->contains($driversLicence)) {
            $this->driversLicences->removeElement($driversLicence);
            $driversLicence->removeDrivingSchool($this);
        }
    }
}
