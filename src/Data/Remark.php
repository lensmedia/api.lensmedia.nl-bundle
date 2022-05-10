<?php

namespace Lens\Bundle\LensApiBundle\Data;

use DateTimeImmutable;
use Symfony\Component\Uid\Ulid;

class Remark
{
    public const DEFAULT = 'default';
    public const INFO = 'info';
    public const QUESTION = 'question';
    public const IMPORTANT = 'important';
    public const WARNING = 'warning';
    public const DANGER = 'danger';

    public Ulid $id; // 01FWGBYM1VGJ9NDC5WG2SDDT56

    public string $level; // default

    public string $remark; // 31-08-2019 Online inkopen aangezet...

    public function __construct()
    {
        $this->id = new Ulid();
    }
}
