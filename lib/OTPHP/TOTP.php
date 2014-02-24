<?php

namespace OTPHP;

use OTPHP\OTP;


class TOTP extends OTP
{
    protected $interval;

    public function __construct($secret, $interval = 30, $digest = 'sha1', $digit = 6) {
        $this->setInterval($interval);
        parent::__construct($secret, $digest, $digit);
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
        return ($otp === $this->at($timestamp));
    }

    public function provisioningURI($name) {
        return "otpauth://totp/".urlencode($name)."?secret={$this->getSecret()}";
    }

    protected function timecode($timestamp) {
        return (int)( (((int)$timestamp * 1000) / ($this->getInterval() * 1000)));
    }

    public function setInterval($interval) {
        $this->interval = $interval;
        return $this;
    }

    public function getInterval() {
        return $this->interval;
    }
}
