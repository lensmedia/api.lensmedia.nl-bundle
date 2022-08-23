<?php

namespace Lens\Bundle\LensApiBundle\Data;

use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

class Remark
{
    public const DEFAULT = 'default';
    public const INFO = 'info';
    public const QUESTION = 'question';
    public const IMPORTANT = 'important';
    public const WARNING = 'warning';
    public const DANGER = 'danger';

    public const LEVELS = [
        self::DEFAULT => self::DEFAULT,
        self::INFO => self::INFO,
        self::QUESTION => self::QUESTION,
        self::IMPORTANT => self::IMPORTANT,
        self::WARNING => self::WARNING,
        self::DANGER => self::DANGER,
    ];

    #[Assert\NotBlank(message: 'remark.id.not_blank')]
    public Ulid $id;

    #[Assert\NotBlank(message: 'remark.level.not_blank')]
    #[Assert\Choice(choices: self::LEVELS, message: 'remark.level.choice')]
    public string $level;

    public ?User $createdBy = null;

    public DateTimeImmutable $createdAt;

    public DateTimeImmutable $updatedAt;

    #[Assert\NotBlank(message: 'remark.remark.not_blank')]
    public string $remark;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->createdAt
            = $this->updatedAt
            = new DateTimeImmutable();
    }
}
