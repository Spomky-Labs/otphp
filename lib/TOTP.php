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
    public function verify($otp, $timestamp = null)
    {
        if($timestamp === null)
            $timestamp = time();

        return $otp === $this->at($timestamp);
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
