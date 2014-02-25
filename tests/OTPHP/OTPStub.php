<?php

namespace OTPHP;

use OTPHP\OTP;

class OTPStub extends OTP
{
	public function provisioningURI()
	{
		return null;
	}
}