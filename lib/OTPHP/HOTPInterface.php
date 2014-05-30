<?php

namespace OTPHP;

use OTPHP\OTPInterface;

interface HOTPInterface extends OTPInterface
{
    /**
     * @return integer The initial counter
     */
    public function getInitialCount();
}
