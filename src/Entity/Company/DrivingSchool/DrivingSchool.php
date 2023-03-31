<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\NearbyDrivingSchool;
use App\Data\DrivingSchoolRegister;
use App\Data\DrivingSchoolRegisterAdmin;
use App\DataFilters\Old\ChamberOfCommerceFilter as OldChamberOfCommerceFilter;
use App\DataFilters\Old\DrivingSchoolSearchFilter as OldDrivingSchoolSearchFilter;
use App\Serializer\AutoContextBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\User;
use Lens\Bundle\LensApiBundle\Repository\DrivingSchoolRepository;

#[ApiResource(
    collectionOperations: [
        'get',
        self::SEARCH_OPERATION => [
            'method' => 'GET',
            'path' => '/driving-schools/search.{_format}',
            'openapi_context' => [
                'description' => 'Search for a driving school using multiple fields ordered by weight.',
                'parameters' => [
                    [
                        'name' => 'q',
                        'in' => 'query',
                        'description' => 'The search term(s) to look for.',
                        'type' => 'string',
                        'required' => true,
                    ],
                ],
            ],
        ],
        'post',
        'register' => [
            'method' => 'POST',
            'path' => '/driving-schools/register.{_format}',
            'input' => DrivingSchoolRegister::class,
            'denormalization_context' => [AutoContextBuilder::DISABLE => true],
            'output' => User::class,
            'normalization_context' => ['groups' => ['user', 'user:item']],
        ],
        'register_admin' => [
            'method' => 'POST',
            'path' => '/driving-schools/register-admin.{_format}',
            'input' => DrivingSchoolRegisterAdmin::class,
            'denormalization_context' => [AutoContextBuilder::DISABLE => true],
            'output' => DrivingSchool::class,
            'normalization_context' => ['groups' => ['user']],
        ],
        'legacy' => [
            'method' => 'GET',
            'path' => '/legacy.{_format}',
            'name' => 'api_driving_schools_get_collection',
            'filters' => [
                OldDrivingSchoolSearchFilter::class,
                OldChamberOfCommerceFilter::class,
            ],
            'normalization_context' => [
                'groups' => ['legacy'],
            ],
        ],
        self::LEGACY_AUTH_OPERATION => [
            'method' => 'GET',
            'path' => '/'.self::LEGACY_AUTH_OPERATION.'.{_format}',
            'name' => 'api_driving_schools_get_collection',
            'normalization_context' => [
                'groups' => ['legacy'],
            ],
        ],
        self::CHAMBER_OF_COMMERCE_AND_NAME => [
            'method' => 'GET',
            'path' => '/driving-schools/chamber-of-commerce-and-name.{_format}',
        ]
    ],
    itemOperations: [
        'nearby' => [
            'method' => 'GET',
            'path' => '/driving-schools/{id}/nearby.{_format}',
            'controller' => NearbyDrivingSchool::class,
            'openapi_context' => [
                'parameters' => [
                    [
                        'in' => 'path',
                        'name' => 'id',
                        'description' => 'Company resource identifier',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                    [
                        'in' => 'query',
                        'name' => 'limit',
                        'description' => 'The number of companies to return',
                        'schema' => [
                            'type' => 'integer',
                            'minimum' => 1,
                            'default' => NearbyDrivingSchool::DEFAULT_LIMIT,
                        ],
                    ],
                ],
            ],
        ],
        'get',
        'patch',
        'delete',
    ],
    normalizationContext: [
        'groups' => ['driving_school'],
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'cbr' => 'exact',
])]
#[ORM\Entity(repositoryClass: DrivingSchoolRepository::class)]
#[ORM\Index(fields: ['cbr'])]
class DrivingSchool extends Company
{
    public const SEARCH_OPERATION = 'search';
    public const LEGACY_AUTH_OPERATION = 'legacy_auth';
    public const CHAMBER_OF_COMMERCE_AND_NAME = 'chamber_of_commerce_and_name';

    #[ORM\Column(length: 32, nullable: true)]
    public ?string $cbr = null;

    /** List of all drivers licences for a driving school that it gives lessons in */
    #[ORM\ManyToMany(targetEntity: DriversLicence::class, inversedBy: 'drivingSchools')]
    #[ApiSubresource(maxDepth: 1)]
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
