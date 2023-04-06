<?php

namespace Lens\Bundle\LensApiBundle\Entity\Personal;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Lens\Bundle\LensApiBundle\Entity\AddressTrait;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;
use Lens\Bundle\LensApiBundle\Entity\ContactMethod;
use Lens\Bundle\LensApiBundle\Entity\ContactMethodTrait;
use Lens\Bundle\LensApiBundle\Entity\Remark;
use Lens\Bundle\LensApiBundle\Entity\User;
use Lens\Bundle\LensApiBundle\Repository\PersonalRepository;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonalRepository::class)]
#[ORM\Index(fields: ['nickname'])]
#[ORM\Index(fields: ['surname'])]
class Personal
{
    use AddressTrait;
    use AdvertisementTrait;
    use ContactMethodTrait;
    use PersonalTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column(nullable: true)]
    public ?string $initials = null;

    #[ORM\Column(nullable: true)]
    public ?string $nickname = null;

    #[ORM\Column(nullable: true)]
    public ?string $surnameAffix = null;

    #[ORM\Column(nullable: true)]
    public ?string $surname = null;

    #[ORM\OneToOne(inversedBy: 'personal', targetEntity: User::class)]
    #[Assert\Valid]
    public ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'personal', targetEntity: ContactMethod::class, cascade: ['all'], orphanRemoval: true)]
    #[Assert\Valid]
    public Collection $contactMethods;

    #[ORM\OneToMany(mappedBy: 'personal', targetEntity: Address::class, orphanRemoval: true)]
    #[Assert\Valid]
    public Collection $addresses;

    #[ORM\OneToMany(mappedBy: 'personal', targetEntity: Employee::class, orphanRemoval: true)]
    #[Assert\Valid]
    public Collection $companies;

    #[ORM\ManyToMany(targetEntity: Advertisement::class, inversedBy: 'personals')]
    #[Assert\Valid]
    public Collection $advertisements;

    #[ORM\OneToMany(mappedBy: 'personal', targetEntity: Remark::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'desc'])]
    #[Assert\Valid]
    public Collection $remarks;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->addresses = new ArrayCollection();
        $this->contactMethods = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->advertisements = new ArrayCollection();
        $this->remarks = new ArrayCollection();
    }

    public function setUser(?User $user): void
    {
        if ($this->user === $user) {
            return;
        }

        $this->user?->setPersonal(null);
        $user?->setPersonal($this);
        $this->user = $user;
    }

    public function addAdvertisement(Advertisement $advertisement): void
    {
        if (!$this->advertisements->contains($advertisement)) {
            $this->advertisements->add($advertisement);
            $advertisement->addPersonal($this);
        }
    }

    public function removeAdvertisement(Advertisement $advertisement): void
    {
        if ($this->advertisements->contains($advertisement)) {
            $this->advertisements->removeElement($advertisement);
            $advertisement->removePersonal($this);
        }
    }

    public function addContactMethod(ContactMethod $contactMethod): void
    {
        if (!$this->contactMethods->contains($contactMethod)) {
            $this->contactMethods->add($contactMethod);
            $contactMethod->setPersonal($this);
        }
    }

    public function removeContactMethod(ContactMethod $contactMethod): void
    {
        if ($this->contactMethods->contains($contactMethod)) {
            $this->contactMethods->removeElement($contactMethod);
            $contactMethod->setPersonal(null);
        }
    }

    public function addAddress(Address $address): void
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setPersonal($this);
        }
    }

    public function removeAddress(Address $address): void
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            $address->setPersonal(null);
        }
    }

    public function addCompany(Employee $employee): void
    {
        if (!$this->companies->contains($employee)) {
            $this->companies->add($employee);
            $employee->setPersonal($this);
            $employee->company?->addEmployee($employee);
        }
    }

    public function removeCompany(Employee $employee): void
    {
        if ($this->companies->contains($employee)) {
            $this->companies->removeElement($employee);
            $employee->company?->removeEmployee($employee);
        }
    }

    public function addRemark(Remark $remark): void
    {
        if (!$this->remarks->contains($remark)) {
            $this->remarks->add($remark);
            $remark->setPersonal($this);
        }
    }

    public function removeRemark(Remark $remark): void
    {
        if ($this->remarks->contains($remark)) {
            $this->remarks->removeElement($remark);
            $remark->setPersonal(null);
        }
    }

    public function displayName(bool $noNickname = false): ?string
    {
        if (!empty($this->nickname) && !$noNickname) {
            return $this->nickname;
        }

        if (!empty($this->initials) && !empty($this->surname)) {
            return $this->initials.($this->surnameAffix ? ' '.$this->surnameAffix : '').' '.$this->surname;
        }

        return null;
    }
}
