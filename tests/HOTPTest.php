<?php

declare(strict_types=1);

namespace OTPHP\Test;

use InvalidArgumentException;
use OTPHP\HOTP;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
final class HOTPTest extends TestCase
{
    #[Test]
    public function labelNotDefined(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The label is not set.');
        $hotp = HOTP::generate();
        $hotp->getProvisioningUri();
    }

    #[Test]
    public function issuerHasColon(): void
    {
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Issuer must not contain a colon.');
        $otp->setIssuer('foo%3Abar');
    }

    #[Test]
    public function issuerHasColon2(): void
    {
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Issuer must not contain a colon.');
        $otp->setIssuer('foo%3abar');
    }

    #[Test]
    public function labelHasColon(): void
    {
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Label must not contain a colon.');
        $otp->setLabel('foo%3Abar');
    }

    #[Test]
    public function labelHasColon2(): void
    {
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Label must not contain a colon.');
        $otp->setLabel('foo:bar');
    }

    #[Test]
    public function digitsIsNot1OrMore(): void
    {
        $htop = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Digits must be at least 1.');
        $htop->setDigits(0);
    }

    #[Test]
    public function counterIsNot1OrMore(): void
    {
        $htop = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Counter must be at least 0.');
        $htop->setCounter(-500);
    }

    #[Test]
    public function digestIsNotSupported(): void
    {
        $htop = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "foo" digest is not supported.');
        $htop->setDigest('foo');
    }

    /**
     * xpectedExceptionMessage.
     */
    #[Test]
    public function secretShouldBeBase32Encoded(): void
    {
        $otp = HOTP::createFromSecret(random_bytes(32));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to decode the secret. Is it correctly base32 encoded?');
        $otp->at(0);
    }

    #[Test]
    public function objectCreationValid(): void
    {
        $otp = HOTP::generate();

        static::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $otp->getSecret());
    }

    #[Test]
    public function getProvisioningUri(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);
        $otp->setParameter('image', 'https://foo.bar/baz');

        static::assertSame(
            'otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y',
            $otp->getProvisioningUri()
        );
    }

    #[Test]
    public function verifyCounterInvalid(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        static::assertFalse($otp->verify('98449994', 100));
    }

    #[Test]
    public function verifyCounterChanged(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1100);

        static::assertTrue($otp->verify('98449994'));
        static::assertFalse($otp->verify('11111111', 1099));
        static::assertSame($otp->getCounter(), 1101);
    }

    #[Test]
    public function verifyValidInWindow(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        static::assertTrue($otp->verify('59647237', 1000, 50));
        static::assertFalse($otp->verify('59647237', 1000, 50));
        static::assertFalse($otp->verify('59647237', 2000, 50));
    }

    /**
     * @param positive-int $digits
     * @param non-empty-string $digest
     * @param 0|positive-int $counter
     * @param non-empty-string $secret
     * @param non-empty-string $label
     * @param non-empty-string $issuer
     */
    private function createHOTP(
        int $digits,
        string $digest,
        int $counter,
        string $secret = 'JDDK4U6G3BJLEZ7Y',
        string $label = 'alice@foo.bar',
        string $issuer = 'My Project'
    ): HOTP {
        $otp = HOTP::createFromSecret($secret);
        $otp->setCounter($counter);
        $otp->setDigest($digest);
        $otp->setDigits($digits);
        $otp->setLabel($label);
        $otp->setIssuer($issuer);

        return $otp;
    }
}
