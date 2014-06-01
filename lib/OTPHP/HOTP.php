<?php

namespace OTPHP;

use OTPHP\OTP;

class HOTP extends OTP implements HOTPInterface
{
    protected $initial_count;

    /**
     * {@inheritdoc}
     * @param integer $initial_count
     */
    public function __construct($secret, $initial_count = 0, $digest = 'sha1', $digit = 6, $issuer = null, $label = null, $issuer_included_as_parameter = true) {
        $this->setInitialCount($initial_count);
        parent::__construct($secret, $digest, $digit, $issuer, $label, $issuer_included_as_parameter);
    }

    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri()
    {
        return $this->generateURI('hotp', array('counter'=>$this->getInitialCount()));
    }

    /**
     * @param integer $initial_count
     * 
     * @return HOTP The object itself for chained calls
     */
    public function setInitialCount($initial_count)
    {
        if(!is_numeric($initial_count) || $initial_count <0 ) {
            throw new \Exception("Initial count must be at least 0.");
        }
        $this->initial_count = $initial_count;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialCount()
    {
        return $this->initial_count;
    }
}
