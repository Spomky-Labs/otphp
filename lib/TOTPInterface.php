<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

interface TOTPInterface extends OTPInterface
{
    /**
     * @return string Return the TOTP at the current time
     */
    public function now();

    /**
     * @return int Get the interval of time for OTP generation (a non-null positive integer, in second)
     */
    public function getInterval();

    /**
     * @param int $interval
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setInterval($interval);
}
