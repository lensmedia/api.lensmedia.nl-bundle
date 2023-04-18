<?php

namespace Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\ResultRepository;
use LogicException;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ORM\Index(fields: ['cbr', 'categoryCode', 'examPeriodStartedAt'])]
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

    #[ORM\Column(type: 'date_immutable')]
    public DateTimeInterface $examPeriodStartedAt;

    #[ORM\Column(type: 'date_immutable')]
    public DateTimeInterface $examPeriodEndedAt;

    #[ORM\Column(options: ['default' => 0])]
    public int $firstExamsSufficientTotal = 0;

    #[ORM\Column(options: ['default' => 0])]
    public int $firstExamsInsufficientTotal = 0;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 4, options: ['default' => 0])]
    public float $firstExamsPercentage = 0;

    #[ORM\Column(options: ['default' => 0])]
    public int $reExamsSufficientTotal = 0;

    #[ORM\Column(options: ['default' => 0])]
    public int $reExamsInsufficientTotal = 0;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 4, options: ['default' => 0])]
    public float $reExamsPercentage = 0;

    /**
     * @var bool Whether this result is locked or not. If locked, it can not be updated.
     *           Used to avoid importing the same data sets multiple times and the numbers stacking.
     */
    #[ORM\Column(type: 'boolean')]
    private bool $locked = false;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    /**
     * Create a new result from a CSV record.
     * @see https://github.com/lensmedia/api.lensmedia.nl/wiki/Cbr-open-data for details on the CSV, and it's format.
     */
    public static function fromCsvRecord(array $record): self
    {
        $values = array_values($record);

        $instance = new static();
        $instance->cbr = $values[0];
        $instance->categoryCode = $values[9];
        $instance->categoryName = $values[10];

        $instance->examPeriodStartedAt = new DateTimeImmutable($values[19]);
        $instance->examPeriodEndedAt = new DateTimeImmutable($values[20]);

        $instance->firstExamsSufficientTotal = $values[21];
        $instance->firstExamsInsufficientTotal = $values[22];

        $instance->reExamsSufficientTotal = $values[29];
        $instance->reExamsInsufficientTotal = $values[30];

        $instance->updatePercentages();

        return $instance;
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function merge(self $result): void
    {
        if ($this->isLocked()) {
            throw new LogicException('Cannot merge with a locked result.');
        }

        if ($this->cbr !== $result->cbr) {
            throw new LogicException('Cannot merge results from different driving schools (CBR ID does not match).');
        }

        $this->firstExamsSufficientTotal += $result->firstExamsSufficientTotal;
        $this->firstExamsInsufficientTotal += $result->firstExamsInsufficientTotal;

        $this->reExamsSufficientTotal += $result->reExamsSufficientTotal;
        $this->reExamsInsufficientTotal += $result->reExamsInsufficientTotal;

        $this->updatePercentages();
    }

    private function updatePercentages(): void
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
}
