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
     * Create a new HOTP object.
     *
     * If the secret is null, a random 64 bytes secret will be generated.
     *
     * @deprecated Deprecated since v11.1, use ::createFromSecret or ::generate instead
     */
    public static function create(
        null|string $secret = null,
        int $counter = 0,
        string $digest = 'sha1',
        int $digits = 6
    ): self;

    /**
     * Create a TOTP object from an existing secret.
     *
     * @param non-empty-string $secret
     */
    public static function createFromSecret(
        string $secret,
        int $counter = 0,
        string $digest = 'sha1',
        int $digits = 6
    ): self;

    /**
     * Create a new HOTP object. A random 64 bytes secret will be generated.
     */
    public static function generate(int $counter = 0, string $digest = 'sha1', int $digits = 6): self;
}
