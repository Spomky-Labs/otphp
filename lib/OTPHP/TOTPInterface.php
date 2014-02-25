<?php

namespace OTPHP;

use OTPHP\OTPInterface;


interface TOTPInterface extends OTPInterface
{
    public function now();
    public function getInterval();
}
