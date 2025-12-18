<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity\PaymentMethod;

use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\DebitRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DebitRepository::class)]
class Debit extends PaymentMethod
{
    public const string METHOD = 'debit';

    #[ORM\Column]
    public string $accountHolder;

    #[Assert\Iban]
    #[ORM\Column]
    public string $iban;

    public function setIban(string $iban): void
    {
        $this->iban = str_replace(' ', '', mb_strtoupper($iban));
    }
}
