<?php

namespace OTPHP;

use OTPHP\OTP;


class TOTP extends OTP implements TOTPInterface
{
    protected $interval;

    /**
     * {@inheritdoc}
     * @param integer $interval
     */
    public function __construct($secret, $interval = 30, $digest = 'sha1', $digit = 6, $issuer = null, $label = null, $issuer_included_as_parameter = true) {
        $this->setInterval($interval);
        parent::__construct($secret, $digest, $digit, $issuer, $label, $issuer_included_as_parameter);
    }

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
        return $this->generateOTP($this->timecode(time()));
    }

    /**
     * {@inheritdoc}
     */
    public function verify($otp, $timestamp = null) {
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
        return (int)( (((int)$timestamp * 1000) / ($this->getInterval() * 1000)));
    }

    /**
     * @param integer $interval
     *
     * @return TOTP The object itself for chained calls
     */
    public function setInterval($interval)
    {
        if (!is_numeric($interval) || $interval < 1) {
            throw new \Exception('The interval must be a positive interger.');
        }
        $this->interval = $interval;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval()
    {
        return $this->interval;
    }
}
