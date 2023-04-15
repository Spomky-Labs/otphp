<?php

declare(strict_types=1);

namespace OTPHP\Test;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * @internal
 */
final class ClockMock implements ClockInterface
{
    private ?DateTimeImmutable $dateTime = null;

    public function now(): DateTimeImmutable
    {
        return $this->dateTime ?? new DateTimeImmutable();
    }

    public function setDateTime(?DateTimeImmutable $dateTime): void
    {
        $this->dateTime = $dateTime;
    }
}
