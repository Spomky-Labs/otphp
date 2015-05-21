<?php

namespace MyProject;

use OTPHP\HOTP as Base;

class HOTP extends Base
{
    use OTP;
    protected $counter = 0;

    public function setCounter($counter)
    {
        if (!is_integer($counter) || $counter < 0) {
            throw new \Exception('Counter must be at least 0.');
        }
        $this->counter = $counter;

        return $this;
    }

    public function getCounter()
    {
        return $this->counter;
    }

    public function updateCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }
}
