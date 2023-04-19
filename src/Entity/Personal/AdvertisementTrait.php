<?php

namespace Lens\Bundle\LensApiBundle\Entity\Personal;

trait AdvertisementTrait
{
    public function mailAdvertisement(): ?Advertisement
    {
        return $this->advertisements->findFirst(
            static fn (int $index, Advertisement $advertisement) => $advertisement->isMail(),
        );
    }

    public function canAdvertiseByMail(): bool
    {
        return null !== $this->mailAdvertisement();
    }

    public function emailAdvertisement(): ?Advertisement
    {
        return $this->advertisements->findFirst(
            static fn (int $index, Advertisement $advertisement) => $advertisement->isEmail(),
        );
    }

    public function canAdvertiseByEmail(): bool
    {
        return null !== $this->emailAdvertisement();
    }
}
