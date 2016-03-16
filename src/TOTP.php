<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

use Assert\Assertion;

final class TOTP extends OTP implements TOTPInterface
{
    /**
     * TOTP constructor.
     *
     * @param string $label
     * @param string $secret
     * @param int    $period
     * @param string $digest
     * @param int    $digits
     */
    public function __construct($label, $secret, $period = 30, $digest = 'sha1', $digits = 6)
    {
        parent::__construct($label, $secret, $digest, $digits);
        $this->setPeriod($period);
    }

    /**
     * @param int $period
     *
     * @return self
     */
    private function setPeriod($period)
    {
        Assertion::integer($period, 'Period must be at least 1.');
        Assertion::greaterThan($period, 0, 'Period must be at least 1.');

        $this->setParameter('period', $period);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeriod()
    {
        return $this->getParameter('period');
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
            return $this->compareOTP($this->at($timestamp), $otp);
        }
        $window = abs($window);

        for ($i = -$window; $i <= $window; ++$i) {
            if ($this->compareOTP($this->at($i * $this->getPeriod() + $timestamp), $otp)) {
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
        $params = [];
        if (true !== $google_compatible || 30 !== $this->getPeriod()) {
            $params = ['period' => $this->getPeriod()];
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
        return (int) ((((int) $timestamp * 1000) / ($this->getPeriod() * 1000)));
    }
}
