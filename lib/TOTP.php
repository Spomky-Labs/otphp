<?php

namespace OTPHP;

abstract class TOTP extends OTP implements TOTPInterface
{
    /**
     * {@inheritdoc}
     */
    public function at($timestamp)
    {
        return $this->generateOTP($this->timecode($timestamp));
    }

    /**
     * {@inheritdoc}
     */
    public function now()
    {
        return $this->at(time());
    }

    /**
     * {@inheritdoc}
     */
    public function verify($otp, $timestamp = null, $window = null)
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        if (!is_integer($window)) {
            return $otp === $this->at($timestamp);
        }

        for ($i=-abs($window); $i <= abs($window); $i++) {
            if ($otp === $this->at($i*$this->getInterval()+$timestamp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri()
    {
        return $this->generateURI('totp', array('period'=>$this->getInterval()));
    }

    /**
     * @param integer $timestamp
     */
    private function timecode($timestamp)
    {
        return (int) ( (((int) $timestamp * 1000) / ($this->getInterval() * 1000)));
    }
}
