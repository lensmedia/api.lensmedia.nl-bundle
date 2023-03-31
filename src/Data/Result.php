<?php

namespace Lens\Bundle\LensApiBundle\Data;

use Lens\Bundle\LensApiBundle\OldApiRepository\LensApiResourceDataInterface;
use Lens\Bundle\LensApiBundle\Validator as Validators;
use DateTimeInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Result implements LensApiResourceDataInterface
{
    #[Assert\NotBlank(message: 'result.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'result.cbr.not_blank')]
    #[Validators\Cbr(message: 'result.cbr.cbr')]
    public string $cbr;

    #[Assert\NotBlank(message: 'result.category_code.not_blank')]
    public string $categoryCode;

    #[Assert\NotBlank(message: 'result.category_name.not_blank')]
    public string $categoryName;

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

    #[Assert\Type(type: 'float', message: 'result.re_exams_insufficient_total.type')]
    public ?float $firstExamsPercentage;

    #[Assert\NotBlank(message: 'result.re_exams_sufficient_total.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_sufficient_total.type')]
    public int $reExamsSufficientTotal = 0;

    #[Assert\NotBlank(message: 'result.re_exams_insufficient_total.not_blank')]
    #[Assert\Type(type: 'integer', message: 'result.re_exams_insufficient_total.type')]
    public int $reExamsInsufficientTotal = 0;

    #[Assert\Type(type: 'float', message: 'result.re_exams_insufficient_total.type')]
    public ?float $reExamsPercentage;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public static function resource(): string
    {
        return 'results';
    }
}
