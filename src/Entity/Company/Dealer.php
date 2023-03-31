<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\DateFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\AddCompanyToDealer;
use App\Controller\DeleteCompanyFromDealer;
use App\Controller\NearbyDealer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\DealerRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: DealerRepository::class)]
#[ORM\Index(columns: ['name'])]
#[ApiResource(
    collectionOperations: [
        'get',
    ],
    itemOperations: [
        'get',
        Dealer::MAP_OPERATION => [
            'method' => 'GET',
            'path' => '/dealers/{id}/map.{_format}',
        ],
        Dealer::NEARBY => [
            'method' => 'GET',
            'path' => '/dealers/{id}/companies/{company}/nearby.{_format}',
            'controller' => NearbyDealer::class,
            'openapi_context' => [
                'parameters' => [
                    [
                        'in' => 'path',
                        'name' => 'id',
                        'description' => 'Dealer resource identifier',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                    [
                        'in' => 'path',
                        'name' => 'company',
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
                            'default' => NearbyDealer::DEFAULT_LIMIT,
                        ],
                    ],
                ],
            ],
        ],
        Dealer::ADD_COMPANY => [
            'method' => 'POST',
            'path' => '/dealers/{id}/companies',
            'controller' => AddCompanyToDealer::class,
            'openapi_context' => [
                'parameters' => [
                    [
                        'in' => 'path',
                        'name' => 'id',
                        'description' => 'Dealer resource identifier',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
        Dealer::DELETE_COMPANY => [
            'method' => 'DELETE',
            'path' => '/dealers/{id}/companies',
            'controller' => DeleteCompanyFromDealer::class,
            'openapi_context' => [
                'parameters' => [
                    [
                        'in' => 'path',
                        'name' => 'id',
                        'description' => 'Dealer resource identifier',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
    ],
    subresourceOperations: [
        'api_companies_dealers_get_subresource' => [
            'normalization_context' => [
                'groups' => ['company'],
            ],
        ],
        'api_driving_schools_dealers_get_subresource' => [
            'normalization_context' => [
                'groups' => ['driving_school'],
            ],
        ],
    ],
    denormalizationContext: [
        'groups' => ['dealer'],
    ],
    normalizationContext: [
        'groups' => ['dealer'],
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name' => 'partial',
])]
#[ApiFilter(DateFilter::class, properties: [
    'companies.publishedAt' => DateFilterInterface::EXCLUDE_NULL,
])]
class Dealer
{
    public const MAP_OPERATION = 'get-map';
    public const NEARBY = 'nearby';
    public const ADD_COMPANY = 'add-company';
    public const DELETE_COMPANY = 'delete-company';

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    #[ApiProperty(identifier: true)]
    public Ulid $id;

    #[ORM\Column(unique: true)]
    public string $name;

    #[ORM\ManyToMany(targetEntity: Company::class, mappedBy: 'dealers')]
    #[ApiSubresource(maxDepth: 1)]
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
