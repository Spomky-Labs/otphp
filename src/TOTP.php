<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
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
     * @param string|null $label
     * @param string|null $secret
     * @param int         $period
     * @param string      $digest
     * @param int         $digits
     */
    public function __construct($label = null, $secret = null, $period = 30, $digest = 'sha1', $digits = 6)
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
     * If no timestamp is provided, the OTP is verified at the actual timestamp
     * {@inheritdoc}
     */
    public function verify($otp, $timestamp = null, $window = null)
    {
        Assertion::string($otp, 'The OTP must be a string');
        Assertion::nullOrInteger($timestamp, 'The timestamp must be null or an integer');
        Assertion::nullOrInteger($window, 'The window parameter must be null or an integer');

        $timestamp = $this->getTimestamp($timestamp);

        if (null === $window) {
            return $this->compareOTP($this->at($timestamp), $otp);
        }

        return $this->verifyOtpWithWindow($otp, $timestamp, $window);
    }

    /**
     * @param string $otp
     * @param int    $timestamp
     * @param int    $window
     *
     * @return bool
     */
    private function verifyOtpWithWindow($otp, $timestamp, $window)
    {
        $window = abs($window);

        for ($i = -$window; $i <= $window; $i++) {
            if ($this->compareOTP($this->at($i * $this->getPeriod() + $timestamp), $otp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int|null $timestamp
     *
     * @return int
     */
    private function getTimestamp($timestamp)
    {
        $timestamp = null === $timestamp ? time() : $timestamp;
        Assertion::greaterOrEqualThan($timestamp, 0, 'Timestamp must be at least 0.');

        return $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri()
    {
        $params = [];
        if (30 !== $this->getPeriod()) {
            $params = ['period' => $this->getPeriod()];
        }

        return $this->generateURI('totp', $params);
    }

    /**
     * @param int $timestamp
     *
     * @return int
     */
    private function timecode($timestamp)
    {
        return (int) floor($timestamp / $this->getPeriod());
    }
}
