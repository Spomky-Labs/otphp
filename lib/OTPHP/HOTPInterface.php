<?php

namespace OTPHP;

use OTPHP\OTPInterface;

interface HOTPInterface extends OTPInterface
{
    public function getInitialCount();
}
