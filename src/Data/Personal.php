<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\LensApiUtil;
use Lens\Bundle\LensApiBundle\Validator\Initials;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Personal
{
    #[Assert\NotBlank(message: 'personal.id.not_blank')]
    public Ulid $id;

    #[Initials(message: 'personal.initials.initials')]
    public ?string $initials = null;

    public ?string $nickname = null;

    public ?string $surnameAffix = null;

    public ?string $surname = null;

    #[Assert\Valid]
    public ?User $user = null;

    /** @var ContactMethod[] */
    #[Assert\Valid]
    public array $contactMethods = [];

    /** @var Address[] */
    #[Assert\Valid]
    public array $addresses = [];

    /** @var Employee[] */
    #[Assert\Valid]
    public array $companies = [];

    /** @var Advertisement[] */
    #[Assert\Valid]
    public array $advertisements = [];

    /** @var Remark[] */
    #[Assert\Valid]
    public array $remarks = [];

    public function displayName(): ?string
    {
        if (!empty($this->nickname)) {
            return $this->nickname;
        }

        if (!empty($this->initials) && !empty($this->surname)) {
            return $this->initials.' '.$this->surname;
        }

        return null;
    }

    public function emailContactMethod(): ?ContactMethod
    {
        return LensApiUtil::ArrayFind(
            static fn(ContactMethod $contactMethod) => 'email' === $contactMethod->method,
            $this->contactMethods,
        );
    }
}
