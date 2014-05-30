<?php

namespace OTPHP;

use OTPHP\OTP;

class OTPStub extends OTP
{
	public function getProvisioningUri()
	{
        return $this->generateURI('test');
	}
}