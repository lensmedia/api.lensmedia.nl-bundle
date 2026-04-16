<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\asd;

use Doctrine\Common\Collections\Collection;
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
     *         international: string
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
                $number = $phoneNumberUtil->parse($contactMethod->value, 'nl');

                $output[$contactMethod->method] = [
                    'national' => $phoneNumberUtil->format($number, PhoneNumberFormat::NATIONAL),
                    'international' => $phoneNumberUtil->format($number, PhoneNumberFormat::INTERNATIONAL),
                ];
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
}
