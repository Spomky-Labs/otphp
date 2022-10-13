<?php

declare(strict_types=1);

namespace OTPHP;

interface TOTPInterface extends OTPInterface
{
    /**
     * Create a new TOTP object.
     *
     * If the secret is null, a random 64 bytes secret will be generated.
     *
     * @deprecated Deprecated since v11.1, use ::createFromSecret or ::generate instead
     */
    public static function create(
        null|string $secret = null,
        int $period = 30,
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
        int $period = 30,
        string $digest = 'sha1',
        int $digits = 6
    ): self;

    /**
     * Create a new TOTP object. A random 64 bytes secret will be generated.
     */
    public static function generate(int $period = 30, string $digest = 'sha1', int $digits = 6): self;

    /**
     * Return the TOTP at the current time.
     */
    public function now(): string;

    /**
     * Get the period of time for OTP generation (a non-null positive integer, in second).
     */
    public function getPeriod(): int;

    public function expiresIn(): int;

    public function getEpoch(): int;
}
