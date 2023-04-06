<?php

namespace Lens\Bundle\LensApiBundle\Entity\Personal;

trait AdvertisementTrait
{
    public function mailAdvertisement(): bool
    {
        return $this->advertisements->findFirst(
            static fn (int $index, Advertisement $advertisement) => $advertisement->isMail(),
        );
    }

    public function emailAdvertisement(): bool
    {
        return $this->advertisements->findFirst(
            static fn (int $index, Advertisement $advertisement) => $advertisement->isEmail(),
        );
    }

    public function canAdvertiseByMail(): bool
    {
        return null !== $this->mailAdvertisement();
    }

    public function canAdvertiseByEmail(): bool
    {
        return null !== $this->mailAdvertisement();
    }
}
