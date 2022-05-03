<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Symfony\Component\Uid\Ulid;

use function Lens\Bundle\LensApiBundle\array_find;

class Personal
{
    public Ulid $id;

    public ?string $initials = null;

    public ?string $nickname = null;

    public ?string $surnameAffix = null;

    public ?string $surname = null;

    public ?User $user = null;

    /** @var ContactMethod[] */
    public array $contactMethods = [];

    /** @var Address[] */
    public array $addresses = [];

    /** @var Employee[] */
    public array $companies = [];

    /** @var Advertisement[] */
    public array $advertisements = [];

    /** @var Remark[] */
    public array $remarks = [];

    public function name(int $companyOffset = 0): ?string
    {
        if (!empty($this->nickname)) {
            return $this->nickname;
        }

        if (!empty($this->initials) || !empty($this->surname)) {
            return $this->initials.' '.$this->surname;
        }

        if (!empty($this->companies[$companyOffset]->company->name)) {
            return $this->companies[$companyOffset]->company->name;
        }

        return null;
    }

    public function emailContactMethod(): ?ContactMethod
    {
        return array_find(
            static fn(ContactMethod $contactMethod) => 'email' === $contactMethod->method,
            $this->contactMethods,
        );
    }
}
