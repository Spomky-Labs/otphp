<?php

namespace OTPHP;

use OTPHP\OTPInterface;


interface TOTPInterface extends OTPInterface
{
    /**
     * @return integer
     */
    public function now();
    public function getInterval();
}
