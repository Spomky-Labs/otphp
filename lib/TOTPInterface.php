<?php

namespace OTPHP;

interface TOTPInterface extends OTPInterface
{
    /**
     * @return integer Return the TOTP at the current time
     */
    public function now();

    /**
     * @return integer Get the interval of time for OTP generation (a non-null positive integer, in second)
     */
    public function getInterval();
}
