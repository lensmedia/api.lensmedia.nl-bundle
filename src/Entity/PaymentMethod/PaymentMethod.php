<?php

namespace Lens\Bundle\LensApiBundle\Entity\PaymentMethod;

use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\PaymentMethodRepository;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'method')]
#[ORM\DiscriminatorMap(PaymentMethod::TYPE_TO_CLASS)]
class PaymentMethod
{
    private const TYPE_TO_CLASS = [
        Debit::METHOD => Debit::class,
        Creditcard::METHOD => Creditcard::class,
    ];

    public const METHODS = [
        Debit::METHOD => Debit::METHOD,
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

    public function getTypes(): string
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
