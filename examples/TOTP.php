<?php

namespace MyProject;

use OTPHP\TOTP as Base;

class TOTP extends Base
{
    use OTP;
    protected $interval = 30;

    public function setInterval($interval)
    {
        if (!is_integer($interval) || $interval < 1) {
            throw new \Exception('Interval must be at least 1.');
        }
        $this->interval = $interval;

        return $this;
    }

    public function getInterval()
    {
        return $this->interval;
    }
}
