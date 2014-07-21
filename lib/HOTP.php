<?php

namespace OTPHP;

use OTPHP\OTP;

abstract class HOTP extends OTP implements HOTPInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri()
    {
        return $this->generateURI('hotp', array('counter'=>$this->getInitialCount()));
    }
}
