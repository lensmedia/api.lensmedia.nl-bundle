<?php

namespace Lens\Bundle\LensApiBundle\Entity\PaymentMethod;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\PaymentMethodInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'method')]
#[ORM\DiscriminatorMap(PaymentMethod::TYPE_TO_CLASS)]
#[ApiResource(
    subresourceOperations: [
        'api_companies_payment_methods_get_subresource' => [
            'normalization_context' => [
                'groups' => ['company'],
            ],
        ],
        'api_driving_schools_payment_methods_get_subresource' => [
            'normalization_context' => [
                'groups' => ['driving_school'],
            ],
        ],
    ],
    normalizationContext: [
        'groups' => ['payment_method'],
    ],
)]
class PaymentMethod
{
    public const TYPE_TO_CLASS = [
        PaymentMethodInterface::DEBIT => Debit::class,
        PaymentMethodInterface::CREDIT_CARD => Creditcard::class,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'paymentMethods')]
    public Company $company;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public function getType(): string
    {
        return array_flip(self::TYPE_TO_CLASS)[static::class];
    }

    public function setCompany(Company $company): void
    {
        if (!isset($this->company) || ($this->company !== $company)) {
            if (!empty($this->company)) {
                $this->company->removePaymentMethod($this);
            }

            $company->addPaymentMethod($this);
            $this->company = $company;
        }
    }
}
