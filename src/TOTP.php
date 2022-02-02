<?php

declare(strict_types=1);

namespace OTPHP;

use Assert\Assertion;
use function Safe\ksort;

final class TOTP extends OTP implements TOTPInterface
{
    protected function __construct(null|string $secret, int $period, string $digest, int $digits, int $epoch = 0)
    {
        parent::__construct($secret, $digest, $digits);
        $this->setPeriod($period);
        $this->setEpoch($epoch);
    }

    public static function create(
        null|string $secret = null,
        int $period = 30,
        string $digest = 'sha1',
        int $digits = 6,
        int $epoch = 0
    ): TOTPInterface {
        return new self($secret, $period, $digest, $digits, $epoch);
    }

    public function getPeriod(): int
    {
        $value = $this->getParameter('period');
        Assertion::integer($value, 'Invalid "epoch" period.');

        return $value;
    }

    public function getEpoch(): int
    {
        $value = $this->getParameter('epoch');
        Assertion::integer($value, 'Invalid "epoch" parameter.');

        return $value;
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
    public function verify(string $otp, null|int $timestamp = null, null|int|float $window = null): bool
    {
        $timestamp = $this->getTimestamp($timestamp);

        if ($window === null) {
            return $this->compareOTP($this->at($timestamp), $otp);
        }

        return $this->verifyOtpWithWindow($otp, $timestamp, $window);
    }

    public function getProvisioningUri(): string
    {
        $params = [];
        if ($this->getPeriod() !== 30) {
            $params['period'] = $this->getPeriod();
        }

        if ($this->getEpoch() !== 0) {
            $params['epoch'] = $this->getEpoch();
        }

        return $this->generateURI('totp', $params);
    }

    protected function setPeriod(int $period): void
    {
        $this->setParameter('period', $period);
    }

    /**
     * @return array<string, callable>
     */
    protected function getParameterMap(): array
    {
        return array_merge(
            parent::getParameterMap(),
            [
                'period' => static function ($value): int {
                    Assertion::greaterThan((int) $value, 0, 'Period must be at least 1.');

                    return (int) $value;
                },
                'epoch' => static function ($value): int {
                    Assertion::greaterOrEqualThan((int) $value, 0, 'Epoch must be greater than or equal to 0.');

                    return (int) $value;
                },
            ]
        );
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function filterOptions(array &$options): void
    {
        parent::filterOptions($options);

        if (isset($options['epoch']) && $options['epoch'] === 0) {
            unset($options['epoch']);
        }

        ksort($options);
    }

    private function setEpoch(int $epoch): void
    {
        $this->setParameter('epoch', $epoch);
    }

    private function verifyOtpWithWindow(string $otp, int $timestamp, int|float $window): bool
    {
        $window = abs($window);

        for ($i = 0; $i <= $window; ++$i) {
            $next = $i * $this->getPeriod() + $timestamp;
            $previous = -$i * $this->getPeriod() + $timestamp;
            $valid = $this->compareOTP($this->at($next), $otp) ||
                $this->compareOTP($this->at($previous), $otp);

            if ($valid) {
                return true;
            }
        }

        return false;
    }

    private function getTimestamp(null|int $timestamp): int
    {
        $timestamp = $timestamp ?? time();
        Assertion::greaterOrEqualThan($timestamp, 0, 'Timestamp must be at least 0.');

        return $timestamp;
    }

    private function timecode(int $timestamp): int
    {
        return (int) floor(($timestamp - $this->getEpoch()) / $this->getPeriod());
    }
}
