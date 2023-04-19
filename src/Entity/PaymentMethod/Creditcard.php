<?php

namespace Lens\Bundle\LensApiBundle\Entity\PaymentMethod;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dummy class, as an example. Please before implementing read carefully about encrypting
 * data stored in this table. As credit card information should be handled very carefully.
 */
#[ORM\Entity]
class Creditcard extends PaymentMethod
{
    public const METHOD = 'creditcard';
}
