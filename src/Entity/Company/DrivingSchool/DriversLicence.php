<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\DriversLicenceRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: DriversLicenceRepository::class)]
#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
    subresourceOperations: [
        'api_driving_schools_drivers_licences_get_subresource' => [
            'normalization_context' => [
                'groups' => ['drivers_licence'],
            ],
        ],
    ],
    denormalizationContext: [
        'groups' => ['drivers_licence'],
    ],
    normalizationContext: [
        'groups' => ['drivers_licence'],
    ],
)]
class DriversLicence
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ApiProperty(identifier: true)]
    public Ulid $id;

    /** Driver's license letter A, AM, B etc.. */
    #[ORM\Column(unique: true)]
    public string $label;

    #[ORM\ManyToMany(targetEntity: DrivingSchool::class, mappedBy: 'driversLicences')]
    public Collection $drivingSchools;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->drivingSchools = new ArrayCollection();
    }

    public function addDrivingSchool(DrivingSchool $drivingSchool): void
    {
        if (!$this->drivingSchools->contains($drivingSchool)) {
            $this->drivingSchools->add($drivingSchool);
            $drivingSchool->addDriversLicence($this);
        }
    }

    public function removeDrivingSchool(DrivingSchool $drivingSchool): void
    {
        if ($this->drivingSchools->contains($drivingSchool)) {
            $this->drivingSchools->removeElement($drivingSchool);
            $drivingSchool->removeDriversLicence($this);
        }
    }
}
