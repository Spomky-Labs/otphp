<?php

declare(strict_types=1);

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
     * @param string|null $secret
     * @param int         $period
     * @param string      $digest
     * @param int         $digits
     */
    protected function __construct(?string $secret, int $period, string $digest, int $digits)
    {
        parent::__construct($secret, $digest, $digits);
        $this->setPeriod($period);
    }

    /**
     * TOTP constructor.
     *
     * @param string|null $secret
     * @param int         $period
     * @param string      $digest
     * @param int         $digits
     *
     * @return self
     */
    public static function create(?string $secret = null, int $period = 30, string $digest = 'sha1', int $digits = 6): self
    {
        return new self($secret, $period, $digest, $digits);
    }

    /**
     * @param int $period
     */
    protected function setPeriod(int $period)
    {
        $this->setParameter('period', $period);
    }

    /**
     * {@inheritdoc}
     */
    public function getPeriod(): int
    {
        return $this->getParameter('period');
    }

    /**
     * {@inheritdoc}
     */
    public function at(int $timestamp): string
    {
        return $this->generateOTP($this->timecode($timestamp));
    }

    /**
     * {@inheritdoc}
     */
    public function now(): string
    {
        return $this->at(time());
    }

    /**
     * If no timestamp is provided, the OTP is verified at the actual timestamp
     * {@inheritdoc}
     */
    public function verify(string $otp, ?int $timestamp = null, ?int $window = null): bool
    {
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
    private function verifyOtpWithWindow(string $otp, int $timestamp, int $window): bool
    {
        $window = abs($window);

        for ($i = -$window; $i <= $window; $i++) {
            $at = (int) $i * $this->getPeriod() + $timestamp;
            if ($this->compareOTP($this->at($at), $otp)) {
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
    private function getTimestamp(?int $timestamp): int
    {
        $timestamp = null === $timestamp ? time() : $timestamp;
        Assertion::greaterOrEqualThan($timestamp, 0, 'Timestamp must be at least 0.');

        return $timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri(): string
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
    private function timecode(int $timestamp): int
    {
        return (int) floor($timestamp / $this->getPeriod());
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterMap(): array
    {
        $v = array_merge(
            parent::getParameterMap(),
            ['period' => function ($value) {
                Assertion::greaterThan((int) $value, 0, 'Period must be at least 1.');

                return (int) $value;
            }]
        );

        return $v;
    }
}
