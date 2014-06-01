<?php

namespace OTPHP;

use OTPHP\OTPInterface;


interface TOTPInterface extends OTPInterface
{
    /**
     * @return integer Return the TOTP at the current time
     */
    public function now();

    /**
     * @return integer Get the interval of time for OTP generation (in second)
     */
    public function getInterval();
}
