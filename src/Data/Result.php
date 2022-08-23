<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\Validator as Validators;
use DateTimeInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Result
{
    #[Assert\NotBlank(message: 'result.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'result.cbr.not_blank')]
    #[Validators\Cbr(message: 'result.cbr.cbr')]
    public string $cbr;

    #[Assert\Valid]
    public ?Company $drivingSchool = null;

    #[Assert\NotBlank(message: 'result.category_code.not_blank')]
    public string $categoryCode;

    #[Assert\NotBlank(message: 'result.category_name.not_blank')]
    public string $categoryName;

    #[Assert\NotBlank(message: 'result.product_code.not_blank')]
    public string $productCode;

    #[Assert\NotBlank(message: 'result.product_name.not_blank')]
    public string $productName;

    #[Assert\NotBlank(message: 'result.exam_location_name.not_blank')]
    public string $examLocationName;

    #[Assert\NotBlank(message: 'result.exam_location_street_name.not_blank')]
    public string $examLocationStreetName;

    #[Assert\NotBlank(message: 'result.exam_location_street_number.not_blank')]
    public string $examLocationStreetNumber;

    #[Assert\NotBlank(message: 'result.exam_location_addition.not_blank')]
    public string $examLocationAddition;

    #[Assert\NotBlank(message: 'result.exam_location_zipcode.not_blank')]
    public string $examLocationZipcode;

    #[Assert\NotBlank(message: 'result.exam_location_city.not_blank')]
    public string $examLocationCity;

    #[Assert\NotBlank(message: 'result.exam_period_started_at.not_blank')]
    #[Assert\DateTime(message: 'result.exam_period_started_at.datetime')]
    public DateTimeInterface $examPeriodStartedAt;

    #[Assert\NotBlank(message: 'result.exam_period_ended_at.not_blank')]
    #[Assert\DateTime(message: 'result.exam_period_ended_at.datetime')]
    public DateTimeInterface $examPeriodEndedAt;

    #[Assert\NotBlank(message: 'result.first_exams_sufficient_total.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_sufficient_total.type')]
    public int $firstExamsSufficientTotal = 0;

    #[Assert\NotBlank(message: 'result.first_exams_insufficient_total.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_insufficient_total.type')]
    public int $firstExamsInsufficientTotal = 0;

    #[Assert\NotBlank(message: 'result.first_exams_sufficient_automatic.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_sufficient_automatic.type')]
    public int $firstExamsSufficientAutomatic = 0;

    #[Assert\NotBlank(message: 'result.first_exams_insufficient_automatic.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_insufficient_automatic.type')]
    public int $firstExamsInsufficientAutomatic = 0;

    #[Assert\NotBlank(message: 'result.first_exams_sufficient_combination.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_sufficient_combination.type')]
    public int $firstExamsSufficientCombination = 0;

    #[Assert\NotBlank(message: 'result.first_exams_insufficient_combination.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_insufficient_combination.type')]
    public int $firstExamsInsufficientCombination = 0;

    #[Assert\NotBlank(message: 'result.first_exams_sufficient_manual.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_sufficient_manual.type')]
    public int $firstExamsSufficientManual = 0;

    #[Assert\NotBlank(message: 'result.first_exams_insufficient_manual.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.first_exams_insufficient_manual.type')]
    public int $firstExamsInsufficientManual = 0;

    #[Assert\NotBlank(message: 'result.re_exams_sufficient_total.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_sufficient_total.type')]
    public int $reExamsSufficientTotal = 0;

    #[Assert\NotBlank(message: 'result.re_exams_insufficient_total.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_insufficient_total.type')]
    public int $reExamsInsufficientTotal = 0;

    #[Assert\NotBlank(message: 'result.re_exams_sufficient_automatic.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_sufficient_automatic.type')]
    public int $reExamsSufficientAutomatic = 0;

    #[Assert\NotBlank(message: 'result.re_exams_insufficient_automatic.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_insufficient_automatic.type')]
    public int $reExamsInsufficientAutomatic = 0;

    #[Assert\NotBlank(message: 'result.re_exams_sufficient_combination.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_sufficient_combination.type')]
    public int $reExamsSufficientCombination = 0;

    #[Assert\NotBlank(message: 'result.re_exams_insufficient_combination.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_insufficient_combination.type')]
    public int $reExamsInsufficientCombination = 0;

    #[Assert\NotBlank(message: 'result.re_exams_sufficient_manual.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_sufficient_manual.type')]
    public int $reExamsSufficientManual = 0;

    #[Assert\NotBlank(message: 'result.re_exams_insufficient_manual.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_insufficient_manual.type')]
    public int $reExamsInsufficientManual = 0;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
