<?php

namespace OTPHP;

interface HOTPInterface extends OTPInterface
{
    /**
     * @return int The initial counter (a positive integer)
     */
    public function getCounter();
}
