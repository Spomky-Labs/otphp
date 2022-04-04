<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

use Assert\Assertion;
use function Safe\ksort;

final class TOTP extends OTP implements TOTPInterface
{
    protected function __construct(?string $secret, int $period, string $digest, int $digits, int $epoch = 0)
    {
        parent::__construct($secret, $digest, $digits);
        $this->setPeriod($period);
        $this->setEpoch($epoch);
    }

    public static function create(?string $secret = null, int $period = 30, string $digest = 'sha1', int $digits = 6, int $epoch = 0): TOTPInterface
    {
        return new self($secret, $period, $digest, $digits, $epoch);
    }

    protected function setPeriod(int $period): void
    {
        $this->setParameter('period', $period);
    }

    public function getPeriod(): int
    {
        return $this->getParameter('period');
    }

    private function setEpoch(int $epoch): void
    {
        $this->setParameter('epoch', $epoch);
    }

    public function getEpoch(): int
    {
        return $this->getParameter('epoch');
    }

    public function at(int $timestamp): string
    {
        return $this->generateOTP($this->timecode($timestamp));
    }

    public function now(): string
    {
        return $this->at(time());
    }

    /**
     * If no timestamp is provided, the OTP is verified at the actual timestamp.
     */
    public function verify(string $otp, ?int $timestamp = null, ?int $window = null): bool
    {
        $timestamp = $this->getTimestamp($timestamp);

        if (null === $window) {
            return $this->compareOTP($this->at($timestamp), $otp);
        }

        return $this->verifyOtpWithWindow($otp, $timestamp, $window) != null;
    }

    public function verifyOtpWithWindow(string $otp, int $timestamp, int $window, ?int $after = null): ?int
    {
        $timecodes = $this->getTimecodesWithWindow($timestamp, $window);

        $afterTimecode = null;
        if ($after != null) {
            $afterTimecode = $this->timecode($after);
        } 

        foreach ($timecodes as $timecode) {
            if ($afterTimecode != null && $timecode <= $afterTimecode) {
                continue;
            }
            if ($this->compareOTP($this->generateOTP($timecode), $otp)) {
                return $this->getTimestampFromTimecode($timecode);
            }
        }

        return null;
    }

    private function getTimecodesWithWindow(int $timestamp, int $window) {
        $window = abs($window);
        
        $timecodes = array($this->timecode($timestamp));
        for ($i = 0; $i <= $window; ++$i) {
            $next = $i * $this->getPeriod() + $timestamp;
            $previous = -$i * $this->getPeriod() + $timestamp;
            $timecodes[] = $this->timecode($previous);
            $timecodes[] = $this->timecode($next);
        }
        return $timecodes;
    }

    private function getTimestamp(?int $timestamp): int
    {
        $timestamp = $timestamp ?? time();
        Assertion::greaterOrEqualThan($timestamp, 0, 'Timestamp must be at least 0.');

        return $timestamp;
    }

    public function getProvisioningUri(): string
    {
        $params = [];
        if (30 !== $this->getPeriod()) {
            $params['period'] = $this->getPeriod();
        }

        if (0 !== $this->getEpoch()) {
            $params['epoch'] = $this->getEpoch();
        }

        return $this->generateURI('totp', $params);
    }

    private function timecode(int $timestamp): int
    {
        return (int) floor(($timestamp - $this->getEpoch()) / $this->getPeriod());
    }

    private function getTimestampFromTimecode(int $timecode): int {
        return $timecode * $this->getPeriod() + $this->getEpoch();
    }


    /**
     * @return array<string, mixed>
     */
    protected function getParameterMap(): array
    {
        $v = array_merge(
            parent::getParameterMap(),
            [
                'period' => function ($value): int {
                    Assertion::greaterThan((int) $value, 0, 'Period must be at least 1.');

                    return (int) $value;
                },
                'epoch' => function ($value): int {
                    Assertion::greaterOrEqualThan((int) $value, 0, 'Epoch must be greater than or equal to 0.');

                    return (int) $value;
                },
            ]
        );

        return $v;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function filterOptions(array &$options): void
    {
        parent::filterOptions($options);

        if (isset($options['epoch']) && 0 === $options['epoch']) {
            unset($options['epoch']);
        }

        ksort($options);
    }
}
