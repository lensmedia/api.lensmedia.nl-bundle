<?php

namespace Lens\Bundle\LensApiBundle\Data;

use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;

use function Lens\Bundle\LensApiBundle\array_any;
use function Lens\Bundle\LensApiBundle\array_find;

class Company
{
    public Ulid $id;

    public string $type;

    public ?string $chamberOfCommerce = null;

    public ?string $name = null;

    public ?string $cbr = null;

    public DateTimeImmutable $createdAt;

    public DateTimeImmutable $updatedAt;

    public ?User $disabledBy = null;

    public ?string $disabledReason = null;

    public ?DateTimeImmutable $disabledAt = null;

    public ?DateTimeImmutable $publishedAt = null;

    /** @var null|Address[] */
    public ?array $addresses = null;

    /** @var null|ContactMethod[] */
    public ?array $contactMethods = null;

    /** @var null|Employee[] */
    public ?array $employees = null;

    /** @var null|Dealer[] */
    public ?array $dealers = null;

    /** @var null|PaymentMethod[] */
    public ?array $paymentMethods = null;

    /** @var null|Remark[] */
    public ?array $remarks = null;

    /** @var null|DriverLicence[] */
    public ?array $driversLicences = null;

    public int $weight = 0; // used in searches not important for other things

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
        return 'driving_school' === $this->type;
    }

    public function isItheoryDealer(): bool
    {
        if (empty($this->dealers)) {
            return false;
        }

        return array_any(
            static fn(Dealer $dealer) => 'itheorie' === $dealer->name,
            $this->dealers,
        );
    }

    public function personal(int $index = 0): ?Personal
    {
        return $this->employees[$index]?->personal;
    }

    public function defaultAddress(): Address
    {
        return array_find(
            static fn(Address $address) => 'default' === $address->type,
            $this->addresses,
        );
    }

    public function billingAddress(): ?Address
    {
        return array_find(
            static fn(Address $address) => 'billing' === $address->type,
            $this->addresses,
        );
    }

    public function operatingAddress(): ?Address
    {
        return array_find(
            static fn(Address $address) => 'operating' === $address->type,
            $this->addresses,
        );
    }

    public function directDebitPaymentMethod(): ?PaymentMethod
    {
        return array_find(
            static fn(PaymentMethod $paymentMethod) => 'debit' === $paymentMethod->type,
            $this->paymentMethods,
        );
    }

    public function emailContactMethod(): ?ContactMethod
    {
        return array_find(
            static fn(ContactMethod $contactMethod) => 'email' === $contactMethod->method,
            $this->contactMethods,
        );
    }

    public function workPhoneContactMethod(): ?ContactMethod
    {
        return array_find(
            static fn(ContactMethod $contactMethod) => 'phone' === $contactMethod->method
                && $contactMethod->label === 'work',
            $this->contactMethods,
        );
    }

    public function mobilePhoneContactMethod(): ?ContactMethod
    {
        return array_find(
            static fn(ContactMethod $contactMethod) => 'phone' === $contactMethod->method
                && $contactMethod->label === 'mobile',
            $this->contactMethods,
        );
    }
}
