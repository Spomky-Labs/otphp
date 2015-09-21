<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

class TOTP extends OTP implements TOTPInterface
{
    /**
     * {@inheritdoc}
     */
    public function setInterval($interval)
    {
        if (!is_int($interval) || $interval < 1) {
            throw new \InvalidArgumentException('Interval must be at least 1.');
        }

        $this->setParameter('interval', $interval);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval()
    {
        return $this->getParameter('interval');
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
        return $this->at(time());
    }

    /**
     * {@inheritdoc}
     */
    public function verify($otp, $timestamp = null, $window = null)
    {
        if (null === $timestamp) {
            $timestamp = time();
        }

        if (!is_int($window)) {
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
    public function getProvisioningUri($google_compatible = true, $custom_parameters = [])
    {
        $params = [];
        if (true !== $google_compatible || 30 !== $this->getInterval()) {
            $params = ['period' => $this->getInterval()];
        }

        return $this->generateURI('totp', $params, $google_compatible, $custom_parameters);
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
