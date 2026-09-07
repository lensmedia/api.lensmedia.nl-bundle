<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Meilisearch;

use Doctrine\Common\Collections\Collection;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

trait MapContactMethodsTrait
{
    /**
     * Remaps our weird objects to a more search-friendly format, especially for phone numbers and social media accounts.
     *
     * @return array{
     *     name?: string,
     *     email?: string,
     *     website?: string,
     *     phone?: array{
     *         national: string,
     *         nationalDigits: string,
     *         international: string,
     *         e164: string,
     *         digits: string
     *     },
     *     socials?: array<string, string>
     * }
     */
    private function mapContactMethods(Collection $collection): array
    {
        static $phoneNumberUtil;
        if (empty($phoneNumberUtil)) {
            $phoneNumberUtil = PhoneNumberUtil::getInstance();
        }

        $output = [];
        /** @var \Lens\Bundle\LensApiBundle\Entity\ContactMethod $contactMethod */
        foreach ($collection as $contactMethod) {
            if ($contactMethod->isPhone()) {
                $output[$contactMethod->method] = $this->mapPhoneNumber($phoneNumberUtil, $contactMethod->value);
            } elseif ($contactMethod->isSocial()) {
                if (!isset($output[$contactMethod->method])) {
                    $output[$contactMethod->method] = [];
                }

                if (null === $contactMethod->label || '' === $contactMethod->label) {
                    continue;
                }

                $output[$contactMethod->method][$contactMethod->label] = $contactMethod->value;
            } else {
                $output[$contactMethod->method] = $contactMethod->value;
            }
        }

        return $output;
    }

    /**
     * Meilisearch splits on separators, so the formatted variants only ever match the way they happen to be
     * grouped. The digit-only variants are what a query normalised the same way can actually hit.
     *
     * @return array{
     *     national: string,
     *     nationalDigits: string,
     *     international: string,
     *     e164: string,
     *     digits: string
     * }
     */
    private function mapPhoneNumber(PhoneNumberUtil $phoneNumberUtil, string $value): array
    {
        try {
            $number = $phoneNumberUtil->parse($value, 'nl');
        } catch (NumberParseException) {
            $digits = preg_replace('/\D+/', '', $value);

            return [
                'national' => $value,
                'nationalDigits' => $digits,
                'international' => $value,
                'e164' => $value,
                'digits' => $digits,
            ];
        }

        $national = $phoneNumberUtil->format($number, PhoneNumberFormat::NATIONAL);
        $e164 = $phoneNumberUtil->format($number, PhoneNumberFormat::E164);

        return [
            'national' => $national,
            'nationalDigits' => preg_replace('/\D+/', '', $national),
            'international' => $phoneNumberUtil->format($number, PhoneNumberFormat::INTERNATIONAL),
            'e164' => $e164,
            'digits' => ltrim($e164, '+'),
        ];
    }
}
