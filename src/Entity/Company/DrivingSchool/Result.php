<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool;

use ApiPlatform\Core\Annotation\ApiResource;
use Lens\Bundle\LensApiBundle\OldApiRepository\ResultRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ApiResource(
    collectionOperations: ['get'],
    subresourceOperations: [
        'api_driving_schools_results_get_subresource' => [
            'normalization_context' => [
                'groups' => ['result'],
            ],
        ],
    ],
    denormalizationContext: [
        'groups' => ['result'],
    ],
    normalizationContext: [
        'groups' => ['result'],
    ],
)]
class Result
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\Column]
    public string $cbr;

    #[ORM\Column]
    public string $categoryCode;

    #[ORM\Column]
    public string $categoryName;

    #[ORM\Column(type: 'datetime_immutable')]
    public DateTimeInterface $examPeriodStartedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    public DateTimeInterface $examPeriodEndedAt;

    #[ORM\Column]
    public int $firstExamsSufficientTotal;

    #[ORM\Column]
    public int $firstExamsInsufficientTotal;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 4, nullable: true)]
    public ?float $firstExamsPercentage;

    #[ORM\Column]
    public int $reExamsSufficientTotal;

    #[ORM\Column]
    public int $reExamsInsufficientTotal;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 4, nullable: true)]
    public ?float $reExamsPercentage;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public function updatePercentages(): void
    {
        $totalFirstExams = $this->firstExamsSufficientTotal + $this->firstExamsInsufficientTotal;
        if ($totalFirstExams > 0) {
            $this->firstExamsPercentage = $this->firstExamsSufficientTotal / $totalFirstExams;
        }

        $totalReExams = $this->reExamsSufficientTotal + $this->reExamsInsufficientTotal;
        if ($totalReExams > 0) {
            $this->reExamsPercentage = $this->reExamsSufficientTotal / $totalReExams;
        }
    }

    /**
     * @link https://github.com/lensmedia/api.lensmedia.nl/wiki/Cbr-open-data Check the
     *       wiki page for index descriptions.
     */
    public static function fromCsvRecord(array $record): self
    {
        $values = array_values($record);

        $instance = new static();
        $instance->cbr = $values[0];
        $instance->categoryCode = $values[9];
        $instance->categoryName = $values[10];

        $instance->examPeriodStartedAt = new DateTimeImmutable($values[19].' 00:00:00');
        $instance->examPeriodEndedAt = new DateTimeImmutable($values[20].' 00:00:00');

        $instance->firstExamsSufficientTotal = $values[21];
        $instance->firstExamsInsufficientTotal = $values[22];

        $instance->reExamsSufficientTotal = $values[29];
        $instance->reExamsInsufficientTotal = $values[30];

        $instance->updatePercentages();

        return $instance;
    }

    public function mergeEntryTotals(Result $result): void
    {
        $this->firstExamsSufficientTotal += $result->firstExamsSufficientTotal;
        $this->firstExamsInsufficientTotal += $result->firstExamsInsufficientTotal;

        $this->reExamsSufficientTotal += $result->reExamsSufficientTotal;
        $this->reExamsInsufficientTotal += $result->reExamsInsufficientTotal;

        $this->updatePercentages();
    }
}
