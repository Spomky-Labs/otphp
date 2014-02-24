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

    public function verify($otp, $timestamp = null, $previous = 0) {
        if($timestamp === null)
            $timestamp = time();

        if (!$previous || !is_numeric($previous) || $previous < 0) {
            return ($otp === $this->at($timestamp));
        }

        for ($i = $previous; $i>=0; $i--) {
            if($otp == $this->at($timestamp - $i*$this->getInterval())) {
                return true;
            }
        }
        return false;
    }

    public function provisioningURI($name) {
        return "otpauth://totp/".urlencode($name)."?secret={$this->getSecret()}";
    }

    private function timecode($timestamp) {
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
