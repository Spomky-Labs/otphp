<?php

declare(strict_types=1);

namespace OTPHP;

use Assert\Assertion;

/**
 * @see \OTPHP\Test\HOTPTest
 */
final class HOTP extends OTP implements HOTPInterface
{
    protected function __construct(null|string $secret, int $counter, string $digest, int $digits)
    {
        parent::__construct($secret, $digest, $digits);
        $this->setCounter($counter);
    }

    public static function create(
        null|string $secret = null,
        int $counter = 0,
        string $digest = 'sha1',
        int $digits = 6
    ): self {
        return new self($secret, $counter, $digest, $digits);
    }

    public function getCounter(): int
    {
        $value = $this->getParameter('counter');
        Assertion::integer($value, 'Invalid "counter" parameter.');

        return $value;
    }

    public function getProvisioningUri(): string
    {
        return $this->generateURI('hotp', [
            'counter' => $this->getCounter(),
        ]);
    }

    /**
     * If the counter is not provided, the OTP is verified at the actual counter.
     */
    public function verify(string $otp, null|int $counter = null, null|int $window = null): bool
    {
        Assertion::greaterOrEqualThan($counter, 0, 'The counter must be at least 0.');

        if ($counter === null) {
            $counter = $this->getCounter();
        } elseif ($counter < $this->getCounter()) {
            return false;
        }

        return $this->verifyOtpWithWindow($otp, $counter, $window);
    }

    protected function setCounter(int $counter): void
    {
        $this->setParameter('counter', $counter);
    }

    /**
     * @return array<string, callable>
     */
    protected function getParameterMap(): array
    {
        return [...parent::getParameterMap(), ...[
            'counter' => static function ($value): int {
                Assertion::greaterOrEqualThan((int) $value, 0, 'Counter must be at least 0.');

                return (int) $value;
            },
        ]];
    }

    private function updateCounter(int $counter): void
    {
        $this->setCounter($counter);
    }

    private function getWindow(null|int $window): int
    {
        return abs($window ?? 0);
    }

    private function verifyOtpWithWindow(string $otp, int $counter, null|int $window): bool
    {
        $window = $this->getWindow($window);

        for ($i = $counter; $i <= $counter + $window; ++$i) {
            if ($this->compareOTP($this->at($i), $otp)) {
                $this->updateCounter($i + 1);

                return true;
            }
        }

        return false;
    }
}
