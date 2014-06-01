<?php

namespace OTPHP;

use OTPHP\OTPInterface;

interface HOTPInterface extends OTPInterface
{
    /**
     * @return integer The initial counter (a positive integer)
     */
    public function getInitialCount();
}
