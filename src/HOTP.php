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

final class HOTP extends OTP implements HOTPInterface
{
    /**
     * HOTP constructor.
     *
     * @param string|null $secret
     * @param int         $counter
     * @param string      $digest
     * @param int         $digits
     */
    protected function __construct(?string $secret, int $counter, string $digest, int $digits)
    {
        parent::__construct($secret, $digest, $digits);
        $this->setCounter($counter);
    }

    /**
     * @param string|null $secret
     * @param int         $counter
     * @param string      $digest
     * @param int         $digits
     *
     * @return self
     */
    public static function create(?string $secret = null, int $counter = 0, string $digest = 'sha1', int $digits = 6): self
    {
        return new self($secret, $counter, $digest, $digits);
    }

    /**
     * @param int $counter
     */
    protected function setCounter(int $counter)
    {
        $this->setParameter('counter', $counter);
    }

    /**
     * {@inheritdoc}
     */
    public function getCounter(): int
    {
        return $this->getParameter('counter');
    }

    /**
     * @param int $counter
     */
    private function updateCounter(int $counter)
    {
        $this->setCounter($counter);
    }

    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri(): string
    {
        return $this->generateURI('hotp', ['counter' => $this->getCounter()]);
    }

    /**
     * If the counter is not provided, the OTP is verified at the actual counter.
     *
     * {@inheritdoc}
     */
    public function verify(string $otp, ?int $counter = null, ?int $window = null): bool
    {
        Assertion::greaterOrEqualThan($counter, 0, 'The counter must be at least 0.');

        if (null === $counter) {
            $counter = $this->getCounter();
        } elseif ($counter < $this->getCounter()) {
            return false;
        }

        return $this->verifyOtpWithWindow($otp, $counter, $window);
    }

    /**
     * @param null|int $window
     *
     * @return int
     */
    private function getWindow(?int $window): int
    {
        return (int) abs($window ?? 0);
    }

    /**
     * @param string   $otp
     * @param int      $counter
     * @param int|null $window
     *
     * @return bool
     */
    private function verifyOtpWithWindow(string $otp, int $counter, ?int $window): bool
    {
        $window = $this->getWindow($window);

        for ($i = $counter; $i <= $counter + $window; $i++) {
            if ($this->compareOTP($this->at($i), $otp)) {
                $this->updateCounter($i + 1);

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameterMap(): array
    {
        $v = array_merge(
            parent::getParameterMap(),
            ['counter' => function ($value) {
                Assertion::greaterOrEqualThan((int) $value, 0, 'Counter must be at least 0.');

                return (int) $value;
            }]
        );

        return $v;
    }
}
