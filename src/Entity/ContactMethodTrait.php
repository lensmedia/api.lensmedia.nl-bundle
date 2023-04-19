<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use Doctrine\Common\Collections\Collection;

trait ContactMethodTrait
{
    public function phoneContactMethod(): ?ContactMethod
    {
        return $this->contactMethods->findFirst(
            static fn (int $index, ContactMethod $contactMethod) => $contactMethod->isPhone()
                && ('mobile' !== $contactMethod->label)
        );
    }

    public function mobilePhoneContactMethod(): ?ContactMethod
    {
        return $this->contactMethods->findFirst(
            static fn (int $index, ContactMethod $contactMethod) => $contactMethod->isPhone()
                && ('mobile' === $contactMethod->label)
        );
    }

    public function emailContactMethod(): ?ContactMethod
    {
        return $this->contactMethods->findFirst(
            static fn (int $index, ContactMethod $contactMethod) => $contactMethod->isEmail()
        );
    }

    public function websiteContactMethod(): ?ContactMethod
    {
        return $this->contactMethods->findFirst(
            static fn (int $index, ContactMethod $contactMethod) => $contactMethod->isWebsite()
        );
    }

    public function socialContactMethods(): Collection
    {
        return $this->contactMethods->filter(
            static fn (int $index, ContactMethod $contactMethod) => $contactMethod->isSocial()
        );
    }
}
