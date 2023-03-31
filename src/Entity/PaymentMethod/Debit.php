<?php

namespace Lens\Bundle\LensApiBundle\Entity\PaymentMethod;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\DebitRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: ['get', 'patch', 'delete'],
    denormalizationContext: ['groups' => ['debit']],
    normalizationContext: ['groups' => ['debit']]
)]
#[ORM\Entity(repositoryClass: DebitRepository::class)]
class Debit extends PaymentMethod
{
    #[ORM\Column]
    public string $accountHolder;

    #[Assert\Iban]
    #[ORM\Column]
    public string $iban;

    public function setIban(string $iban): void
    {
        $this->iban = str_replace(' ', '', strtoupper($iban));
    }
}
