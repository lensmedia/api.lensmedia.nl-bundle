<?php

namespace Lens\Bundle\LensApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Lens\Bundle\LensApiBundle\Repository\ContactMethodRepository;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use function in_array;

use const FILTER_VALIDATE_URL;

#[ORM\Entity(repositoryClass: ContactMethodRepository::class)]
#[ORM\Index(fields: ['value'])]
class ContactMethod
{
    public const UNDEFINED = 'undefined';
    public const PHONE = 'phone';
    public const EMAIL = 'email';
    public const WEBSITE = 'website';
    public const SOCIAL = 'social';

    public const METHODS = [
        self::UNDEFINED => self::UNDEFINED,
        self::PHONE => self::PHONE,
        self::EMAIL => self::EMAIL,
        self::WEBSITE => self::WEBSITE,
        self::SOCIAL => self::SOCIAL,
    ];

    public const SOCIAL_LABELS = [
        'LinkedIn' => 'linkedin',
        'Twitter' => 'twitter',
        'Facebook' => 'facebook',
        'Instagram' => 'instagram',
        'TikTok' => 'tiktok',
        'Snapchat' => 'snapchat',
        'YouTube' => 'youtube',
        'Telegram' => 'telegram',
        'Signal' => 'signal',
        'Discord' => 'discord',
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column]
    #[Assert\Choice(choices: self::METHODS)]
    public string $method = self::UNDEFINED;

    /**
     * The actual value of chosen contact method (eg email address,
     * phone number, etc).
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    public string $value;

    /**
     * Label for custom types and remarks. Mainly to differentiate
     * between e.g. home and mobile phone, or Facebook and Twitter etc.
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
        ?string $label = null,
    ) {
        $this->id = new Ulid();

        $this->method = $method;
        $this->value = $value;
        $this->label = $label;
    }

    public function isUndefined(): bool
    {
        return self::UNDEFINED === $this->method;
    }

    public function isPhone(): bool
    {
        return self::PHONE === $this->method;
    }

    public function isEmail(): bool
    {
        return self::EMAIL === $this->method;
    }

    public function isWebsite(): bool
    {
        return self::WEBSITE === $this->method;
    }

    public function isSocial(): bool
    {
        return self::SOCIAL === $this->method;
    }

    public function setPersonal(?Personal $personal): void
    {
        if ($this->personal === $personal) {
            return;
        }

        $this->personal?->removeContactMethod($this);
        $this->personal = $personal;
        $personal?->addContactMethod($this);
    }

    public function setCompany(?Company $company): void
    {
        if ($this->company === $company) {
            return;
        }

        $this->company?->removeContactMethod($this);
        $this->company = $company;
        $company?->addContactMethod($this);
    }

    #[Assert\Callback]
    public function validateValue(ExecutionContextInterface $context, $payload): void
    {
        if (!$this->hasValidType()) {
            return;
        }

        match ($this->method) {
            self::WEBSITE => $this->isValidDomain($context, $payload),
            self::EMAIL => $this->isValidEmail($context, $payload),
            self::PHONE => $this->isValidPhoneNumber($context, $payload),

            // All other types are valid by default, can't really validate that based on random user input.
            default => null,
        };
    }

    private function hasValidType(): bool
    {
        return in_array($this->method, self::METHODS, true);
    }

    private function isValidDomain(ExecutionContextInterface $context, $payload): void
    {
        if (filter_var($this->value, FILTER_VALIDATE_URL)) {
            /* @noinspection HttpUrlsUsage */
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

    public function __toString(): string
    {
        return $this->value;
    }
}
