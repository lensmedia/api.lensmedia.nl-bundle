<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Entity\Company;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Lens\Bundle\LensApiBundle\Repository\DealerRepository;
use Symfony\Component\Uid\Ulid;

/**
 * Associates a Company with a Dealer, along with a timestamp of the last interaction.
 */
#[ORM\Entity(repositoryClass: DealerRepository::class)]
#[ORM\UniqueConstraint(fields: ['supplier', 'dealer'])]
class Dealer
{
    /**
     * The threshold to consider a dealer inactive. Note that these records should not exist anymore
     * after the cleanup cron has run, this is just used as a reference to use in said cron job.
     *
     * Tldr; record exists means active, record does not exist means inactive.
     */
    public const string INACTIVITY_THRESHOLD = '-2 years';

    #[ORM\Id]
    #[ORM\Column(type: 'ulid')]
    public Ulid $id;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'dealers')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public Company $dealer;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'suppliers')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    public Company $supplier;

    /**
     * @var DateTimeImmutable the timestamp of the last purchase for this company from this dealer
     */
    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    public DateTimeImmutable $timestamp;

    /**
     * @var bool indicates whether this dealer-company association should be considered active regardless of timestamp
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $forceActive = false;

    public function __construct()
    {
        $this->id = new Ulid();
        $this->timestamp = new DateTimeImmutable();
    }

    public static function mark(Company $company, Company $purchasedFrom, ?DateTimeImmutable $timestamp = null): self
    {
        $entity = new self();
        $entity->dealer = $company;
        $entity->supplier = $purchasedFrom;
        $entity->timestamp = $timestamp ?? new DateTimeImmutable();

        return $entity;
    }

    public function update(?DateTimeImmutable $timestamp = null): void
    {
        $timestamp ??= new DateTimeImmutable();

        if ($this->timestamp < $timestamp) {
            $this->timestamp = $timestamp;
        }
    }
}
