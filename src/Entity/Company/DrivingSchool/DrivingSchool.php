<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Repository\DrivingSchoolRepository;

#[ORM\Entity(repositoryClass: DrivingSchoolRepository::class)]
#[ORM\Index(fields: ['cbr'])]
class DrivingSchool extends Company
{
    #[ORM\Column(length: 32, nullable: true)]
    public ?string $cbr = null;

    /** List of all drivers licences for a driving school that it gives lessons in */
    #[ORM\ManyToMany(targetEntity: DriversLicence::class, inversedBy: 'drivingSchools')]
    public Collection $driversLicences;

    public function __construct()
    {
        parent::__construct();

        $this->driversLicences = new ArrayCollection();
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
