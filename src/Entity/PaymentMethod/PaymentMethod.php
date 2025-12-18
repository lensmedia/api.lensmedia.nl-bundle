<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity\PaymentMethod;

use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Repository\PaymentMethodRepository;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'method')]
#[ORM\DiscriminatorMap(PaymentMethod::TYPE_TO_CLASS)]
class PaymentMethod
{
    private const array TYPE_TO_CLASS = [
        Debit::METHOD => Debit::class,
        Creditcard::METHOD => Creditcard::class,
    ];

    public const array METHODS = [
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

            $this->company = $company;
            $company->addPaymentMethod($this);
        }
    }
}
