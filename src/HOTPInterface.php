<?php

declare(strict_types=1);

namespace OTPHP;

interface HOTPInterface extends OTPInterface
{
    /**
     * The initial counter (a positive integer).
     */
    public function getCounter(): int;

    /**
     * Create a new TOTP object.
     *
     * If the secret is null, a random 64 bytes secret will be generated.
     */
    public static function create(
        null|string $secret = null,
        int $counter = 0,
        string $digest = 'sha1',
        int $digits = 6
    ): self;
}
