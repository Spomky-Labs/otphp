<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

interface TOTPInterface extends OTPInterface
{
    /**
     * Create a new TOTP object.
     *
     * If the secret is null, a random 64 bytes secret will be generated.
     *
     * @param string|null $secret
     * @param int         $period
     * @param string      $digest
     * @param int         $digits
     *
     * @return TOTPInterface
     */
    public static function create(?string $secret = null, int $period = 30, string $digest = 'sha1', int $digits = 6): TOTPInterface;

    /**
     * Return the TOTP at the current time
     *
     * @return string
     */
    public function now(): string;

    /**
     * Get the period of time for OTP generation (a non-null positive integer, in second)
     *
     * @return int
     */
    public function getPeriod(): int;


    /**
     * @return int
     */
    public function getEpoch(): int;
}
