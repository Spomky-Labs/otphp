<?php

namespace OTPHP;

use OTPHP\OTP;

class HOTP extends OTP
{
    public function provisioningURI($name, $initial_count, $issuer = null)
    {
        return $this->generateURI('hotp', $name, $issuer, array('counter'=>$initial_count));
    }
}
