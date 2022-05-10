<?php

namespace Lens\Bundle\LensApiBundle\Data;

use DateTimeInterface;
use Symfony\Component\Uid\Ulid;

class Result
{
    public Ulid $id;

    public string $cbr;

    public ?Company $drivingSchool = null;

    public string $categoryCode;

    public string $categoryName;

    public string $productCode;

    public string $productName;

    public string $examLocationName;

    public string $examLocationStreetName;

    public string $examLocationStreetNumber;

    public string $examLocationAddition;

    public string $examLocationZipcode;

    public string $examLocationCity;

    public DateTimeInterface $examPeriodStartedAt;

    public DateTimeInterface $examPeriodEndedAt;

    public int $firstExamsSufficientTotal;

    public int $firstExamsInsufficientTotal;

    public int $firstExamsSufficientAutomatic;

    public int $firstExamsInsufficientAutomatic;

    public int $firstExamsSufficientCombination;

    public int $firstExamsInsufficientCombination;

    public int $firstExamsSufficientManual;

    public int $firstExamsInsufficientManual;

    public int $reExamsSufficientTotal;

    public int $reExamsInsufficientTotal;

    public int $reExamsSufficientAutomatic;

    public int $reExamsInsufficientAutomatic;

    public int $reExamsSufficientCombination;

    public int $reExamsInsufficientCombination;

    public int $reExamsSufficientManual;

    public int $reExamsInsufficientManual;

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
