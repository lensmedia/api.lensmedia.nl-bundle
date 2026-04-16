<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\asd\Data;

use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Entity\User;

use function sprintf;

class UserPersonalData
{
    private function __construct(
        public ?User $user = null,
        public ?Personal $personal = null,
    ) {
    }

    public static function fromUserAndPersonal(User $user, Personal $personal): self
    {
        return new self($user, $personal);
    }

    public static function fromUser(User $user): self
    {
        return new self($user, $user->personal);
    }

    public static function fromPersonal(Personal $personal): self
    {
        return new self($personal->user, $personal);
    }

    public function id(): string
    {
        if ($this->user && $this->personal) {
            return sprintf('%s_%s', $this->user->id, $this->personal->id);
        }

        return $this->user?->id.$this->personal?->id;
    }
}
