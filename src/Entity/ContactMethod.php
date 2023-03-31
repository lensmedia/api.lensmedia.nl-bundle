<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Lens\Bundle\LensApiBundle\ContactMethodInterface;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\ContactMethodRepository;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    collectionOperations: ['get', 'post'],
    itemOperations: ['get', 'patch', 'delete'],
    subresourceOperations: [
        'api_companies_contact_methods_get_subresource' => [
            'normalization_context' => [
                'groups' => ['company'],
            ],
        ],
        'api_driving_schools_contact_methods_get_subresource' => [
            'normalization_context' => [
                'groups' => ['driving_school'],
            ],
        ],
    ],
    denormalizationContext: [
        'groups' => ['contact_method'],
    ],
    normalizationContext: [
        'groups' => ['contact_method'],
    ],
)]
#[ORM\Entity(repositoryClass: ContactMethodRepository::class)]
#[ORM\Index(fields: ['value'])]
class ContactMethod
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column]
    #[Assert\Choice(choices: ContactMethodInterface::METHODS)]
    public string $method = ContactMethodInterface::UNDEFINED;

    /**
     * The actual value of chosen contact method (eg email address,
     * phone number, etc).
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $value;

    /**
     * Label for custom types and remarks. Mainly to differentiate
     * between eg home and mobile phone, or Facebook and Twitter etc.
     */
    #[ORM\Column(nullable: true)]
    public ?string $label = null;

    #[ORM\ManyToOne(targetEntity: Personal::class, inversedBy: 'contactMethods')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?Personal $personal = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'contactMethods')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?Company $company = null;

    public function __construct(
        string $method,
        string $value,
        string $label = null,
    ) {
        $this->id = new Ulid();

        $this->method = $method;
        $this->value = $value;
        $this->label = $label;
    }

    public function setPersonal(?Personal $personal): void
    {
        if ($this->personal === $personal) {
            return;
        }

        $this->personal?->removeContactMethod($this);
        $personal?->addContactMethod($this);
        $this->personal = $personal;
    }

    public function setCompany(?Company $company): void
    {
        if ($this->company === $company) {
            return;
        }

        $this->company?->removeContactMethod($this);
        $company?->addContactMethod($this);
        $this->company = $company;
    }

    #[Assert\Callback]
    public function validateValue(ExecutionContextInterface $context, $payload): void
    {
        if (!$this->hasValidType()) {
            return;
        }

        match ($this->method) {
            ContactMethodInterface::WEBSITE => $this->isValidDomain($context, $payload),
            ContactMethodInterface::EMAIL => $this->isValidEmail($context, $payload),
            ContactMethodInterface::PHONE => $this->isValidPhoneNumber($context, $payload),
        };
    }

    private function hasValidType(): bool
    {
        return in_array($this->method, ContactMethodInterface::METHODS, true);
    }

    private function isValidDomain(ExecutionContextInterface $context, $payload): void
    {
        if (filter_var($this->value, FILTER_VALIDATE_URL)) {
            /** @noinspection HttpUrlsUsage */
            if (str_starts_with($this->value, 'http://') || str_starts_with($this->value, 'https://')) {
                return;
            }
        }

        $context->buildViolation('Invalid domain given "{{ domain }}".')
            ->setParameter('{{ domain }}', $this->value)
            ->addViolation();
    }

    private function isValidEmail(ExecutionContextInterface $context, $payload): void
    {
        $validator = new EmailValidator();

        if (!$validator->isValid($this->value, new RFCValidation())) {
            $context->buildViolation('The email "{{ email }}" is not a valid email address.')
                ->setParameter('{{ email }}', $this->value)
                ->addViolation();
        }
    }

    private function isValidPhoneNumber(ExecutionContextInterface $context, $payload): void
    {
        $phoneUtils = PhoneNumberUtil::getInstance();
        $phoneProto = $phoneUtils->parse($this->value);

        if (!$phoneProto || !$phoneUtils->isValidNumber($phoneProto)) {
            $context->buildViolation('Given number "{{ phone_number }}" is not a valid phone number.')
                ->setParameter('{{ phone_number }}', $this->value)
                ->addViolation();
        }
    }
}
