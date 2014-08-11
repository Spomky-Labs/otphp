<?php

namespace OTPHP;

interface HOTPInterface extends OTPInterface
{
    /**
     * @return integer The initial counter (a positive integer)
     */
    public function getCounter();
}
