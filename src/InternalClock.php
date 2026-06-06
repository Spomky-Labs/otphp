<?php

declare(strict_types=1);

namespace OTPHP;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * @readonly
 *
 * @internal
 */
final class InternalClock implements ClockInterface
{
    /**
     * Returns the current time as a DateTimeImmutable object.
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
