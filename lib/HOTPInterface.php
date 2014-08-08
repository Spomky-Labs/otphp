<?php

namespace OTPHP;

use OTPHP\OTPInterface;

interface HOTPInterface extends OTPInterface
{
    /**
     * @return integer The initial counter (a positive integer)
     *
     * @deprecated since 3.0, will be removed from 4.0. Use getCounter() instead
     */
    public function getInitialCount();

    /**
     * @return integer The initial counter (a positive integer)
     */
    public function getCounter();
}
