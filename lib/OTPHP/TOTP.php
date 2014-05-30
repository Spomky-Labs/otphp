<?php

namespace OTPHP;

use OTPHP\OTP;


class TOTP extends OTP implements TOTPInterface
{
    protected $interval;

    public function __construct($secret, $interval = 30, $digest = 'sha1', $digit = 6, $issuer = null, $label = null) {
        $this->setInterval($interval);
        parent::__construct($secret, $digest, $digit, $issuer, $label);
    }

    public function at($timestamp)
    {
        return $this->generateOTP($this->timecode($timestamp));
    }

    public function now()
    {
        return $this->generateOTP($this->timecode(time()));
    }

    public function verify($otp, $timestamp = null) {
        if($timestamp === null)
            $timestamp = time();

        return $otp === $this->at($timestamp);
    }

    public function provisioningURI($label = null, $issuer = null)
    {
        if($label !== null) $this->setLabel($label);
        if($issuer !== null) $this->setIssuer($issuer);
        return $this->generateURI('totp', array('period'=>$this->getInterval()));
    }

    private function timecode($timestamp)
    {
        return (int)( (((int)$timestamp * 1000) / ($this->getInterval() * 1000)));
    }

    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    public function getInterval()
    {
        return $this->interval;
    }
}
