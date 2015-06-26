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
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        if (!is_integer($window)) {
            return $otp === $this->at($timestamp);
        }
        $window = abs($window);

        for ($i = -$window; $i <= $window; ++$i) {
            if ($otp === $this->at($i * $this->getInterval() + $timestamp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri($google_compatible = true)
    {
        $params = array();
        if (true !== $google_compatible || 30 !== $this->getInterval()) {
            $params = array('period' => $this->getInterval());
        }

        return $this->generateURI('totp', $params, $google_compatible);
    }

    /**
     * @param int $timestamp
     *
     * @return int
     */
    private function timecode($timestamp)
    {
        return (int) ((((int) $timestamp * 1000) / ($this->getInterval() * 1000)));
    }
}
