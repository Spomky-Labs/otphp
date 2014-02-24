<?php

namespace OTPHP;

use OTPHP\OTP;

class HOTP extends OTP
{
    public function provisioningURI($name, $initial_count)
    {
        return "otpauth://hotp/".urlencode($name)."?secret={$this->getSecret()}&counter=$initial_count";
    }
}
