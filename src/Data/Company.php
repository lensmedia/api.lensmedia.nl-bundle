<?php

namespace Lens\Bundle\LensApiBundle\Data;

use DateTimeImmutable;
use Lens\Bundle\LensApiBundle\LensApiUtil;
use Lens\Bundle\LensApiBundle\Validator as Validators;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Company
{
    public const COMPANY = 'company';
    public const DRIVING_SCHOOL = 'driving_school';
    public const TYPES = [
        self::COMPANY => self::COMPANY,
        self::DRIVING_SCHOOL => self::DRIVING_SCHOOL,
    ];

    #[Assert\NotBlank(message: 'company.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'company.type.not_blank')]
    #[Assert\Choice(choices: self::TYPES, message: 'company.type.choice')]
    public string $type = self::COMPANY;

    #[Validators\ChamberOfCommerce(message: 'company.chamber_of_commerce.chamber_of_commerce')]
    public ?string $chamberOfCommerce = null;

    #[Assert\NotBlank(message: 'company.name.not_blank')]
    public string $name;

    #[Validators\Cbr(message: 'company.cbr.cbr')]
    public ?string $cbr = null;

    #[Assert\NotBlank(message: 'company.created_at.not_blank')]
    #[Assert\DateTime(message: 'company.created_at.datetime')]
    public DateTimeImmutable $createdAt;

    #[Assert\NotBlank(message: 'company.updated_at.not_blank')]
    #[Assert\DateTime(message: 'company.updated_at.datetime')]
    public DateTimeImmutable $updatedAt;

    public ?string $disabledBy = null;

    public ?string $disabledReason = null;

    #[Assert\DateTime(message: 'company.disabled_at.datetime')]
    public ?DateTimeImmutable $disabledAt = null;

    #[Assert\DateTime(message: 'company.enabled_at.datetime')]
    public ?DateTimeImmutable $publishedAt = null;

    /** @var null|Address[] */
    #[Assert\Valid]
    public ?array $addresses = null;

    /** @var null|ContactMethod[] */
    #[Assert\Valid]
    public ?array $contactMethods = null;

    /** @var null|Employee[] */
    #[Assert\Valid]
    public ?array $employees = null;

    /** @var null|Dealer[] */
    #[Assert\Valid]
    public ?array $dealers = null;

    /** @var null|PaymentMethod[] */
    #[Assert\Valid]
    public ?array $paymentMethods = null;

    /** @var null|Remark[] */
    #[Assert\Valid]
    public ?array $remarks = null;

    /** @var null|DriversLicence[] */
    #[Assert\Valid]
    public ?array $driversLicences = null;

    /** @var null|Result[] */
    #[Assert\Valid]
    public ?array $results = null;

    // These are custom query returned properties
    public ?int $weight = null;

    public ?string $distance = null;

    public function isPublished(): bool
    {
        return $this->publishedAt !== null
            && new DateTimeImmutable() > $this->publishedAt;
    }

    public function isDisabled(): bool
    {
        return $this->disabledAt !== null
            && new DateTimeImmutable() > $this->disabledAt;
    }

    public function isDrivingSchool(): bool
    {
        return self::DRIVING_SCHOOL === $this->type;
    }

    public function isItheoryDealer(): bool
    {
        if (empty($this->dealers)) {
            return false;
        }

        return LensApiUtil::ArrayAny(
            static fn(Dealer $dealer) => 'itheorie' === $dealer->name,
            $this->dealers,
        );
    }

    public function personal(int $index = 0): ?Personal
    {
        return $this->employees[$index]?->personal;
    }

    public function defaultAddress(): ?Address
    {
        if (!$this->addresses) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(Address $address) => Address::DEFAULT === $address->type,
            $this->addresses,
        );
    }

    public function billingAddress(): ?Address
    {
        if (!$this->addresses) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(Address $address) =>
                Address::BILLING === $address->type,
            $this->addresses,
        );
    }

    public function shippingAddress(): ?Address
    {
        if (!$this->addresses) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(Address $address) =>
                Address::SHIPPING === $address->type,
            $this->addresses,
        );
    }

    public function operatingAddress(): ?Address
    {
        if (!$this->addresses) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(Address $address) =>
                Address::OPERATING === $address->type,
            $this->addresses,
        );
    }

    public function directDebitPaymentMethod(): ?PaymentMethod
    {
        if (!$this->contactMethods) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(PaymentMethod $paymentMethod) =>
                Paymentmethod::DEBIT === $paymentMethod->type,
            $this->paymentMethods,
        );
    }

    public function emailContactMethod(): ?ContactMethod
    {
        if (!$this->contactMethods) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(ContactMethod $contactMethod) =>
                ContactMethod::EMAIL === $contactMethod->method,
            $this->contactMethods,
        );
    }

    public function workPhoneContactMethod(): ?ContactMethod
    {
        if (!$this->contactMethods) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(ContactMethod $contactMethod) =>
                ContactMethod::PHONE === $contactMethod->method
                && $contactMethod->label === 'work',
            $this->contactMethods,
        );
    }

    public function mobilePhoneContactMethod(): ?ContactMethod
    {
        if (!$this->contactMethods) {
            return null;
        }

        return LensApiUtil::ArrayFind(
            static fn(ContactMethod $contactMethod) =>
                ContactMethod::PHONE === $contactMethod->method
                && $contactMethod->label === 'mobile',
            $this->contactMethods,
        );
    }

    public function disable(string $reason, ?string $user = null): void
    {
        $this->disabledBy = $user;
        $this->disabledReason = $reason;
        $this->disabledAt = new DateTimeImmutable();
    }

    public function enable(): void
    {
        $this->disabledBy = null;
        $this->disabledReason = null;
        $this->disabledAt = null;
    }
}
