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
        $this->expectExceptionMessage('The label is not set. Either label or issuer must be set.');
        $hotp = HOTP::generate();
        $hotp->getProvisioningUri();
    }

    #[Test]
    public function provisioningUriWithIssuerOnly(): void
    {
        $hotp = HOTP::generate();
        $hotp->setIssuer('My Issuer');

        $uri = $hotp->getProvisioningUri();

        static::assertStringContainsString('otpauth://hotp/My%20Issuer?', $uri);
        static::assertStringContainsString('issuer=My%20Issuer', $uri);
    }

    #[Test]
    public function provisioningUriWithLabelOnly(): void
    {
        $hotp = HOTP::generate();
        $hotp->setLabel('alice@foo.bar');

        $uri = $hotp->getProvisioningUri();

        static::assertStringContainsString('otpauth://hotp/alice%40foo.bar?', $uri);
        static::assertStringNotContainsString('issuer=', $uri);
    }

    #[Test]
    public function provisioningUriWithIssuerAndLabel(): void
    {
        $hotp = HOTP::generate();
        $hotp->setIssuer('My Project');
        $hotp->setLabel('alice@foo.bar');

        $uri = $hotp->getProvisioningUri();

        static::assertStringContainsString('otpauth://hotp/My%20Project%3Aalice%40foo.bar?', $uri);
        static::assertStringContainsString('issuer=My%20Project', $uri);
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
    public function labelSimpleAccountName(): void
    {
        // Valid: simple account name per spec example
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $otp->setLabel('alice@gmail.com');

        static::assertSame('alice@gmail.com', $otp->getLabel());
    }

    #[Test]
    public function labelWithLiteralColonAndSpace(): void
    {
        // Valid per spec: Provider1:Alice%20Smith
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $otp->setLabel('Provider1:Alice%20Smith');

        static::assertSame('Provider1:Alice%20Smith', $otp->getLabel());
    }

    #[Test]
    public function labelWithEncodedColonAndSpaces(): void
    {
        // Valid per spec: Big%20Corporation%3A%20alice%40bigco.com
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $otp->setLabel('Big%20Corporation%3A%20alice%40bigco.com');

        static::assertSame('Big%20Corporation%3A%20alice%40bigco.com', $otp->getLabel());
    }

    #[Test]
    public function labelIssue225Format(): void
    {
        // Issue #225: Provider%3Ausername%40domain.com
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $otp->setLabel('Provider%3Ausername%40domain.com');

        static::assertSame('Provider%3Ausername%40domain.com', $otp->getLabel());
    }

    #[Test]
    public function labelWithLiteralColonSeparator(): void
    {
        // Valid: issuer:account format
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $otp->setLabel('Provider:username@domain.com');

        static::assertSame('Provider:username@domain.com', $otp->getLabel());
    }

    #[Test]
    public function labelWithMultipleColonsInvalid(): void
    {
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Neither issuer nor account name in label may contain a colon.');
        $otp->setLabel('foo:bar:baz');
    }

    #[Test]
    public function labelWithColonInAccountPartInvalid(): void
    {
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Neither issuer nor account name in label may contain a colon.');
        $otp->setLabel('Provider:user:name@domain.com');
    }

    #[Test]
    public function withLabelMethod(): void
    {
        // Test withLabel() method (readonly pattern)
        $otp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $newOtp = $otp->withLabel('Provider%3Ausername@domain.com');

        static::assertSame('Provider%3Ausername@domain.com', $newOtp->getLabel());
        static::assertNull($otp->getLabel()); // Original unchanged
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
        $readonlyOtp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y')
            ->withCounter(1000)
            ->withDigest('sha1')
            ->withDigits(8)
            ->withLabel('alice@foo.bar')
            ->withIssuer('My Project')
            ->withParameter('image', 'https://foo.bar/baz')
        ;

        $expectedUri = 'otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        static::assertSame($expectedUri, $readonlyOtp->getProvisioningUri());

        $mutableOtp = HOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $mutableOtp->setCounter(1000);
        $mutableOtp->setDigest('sha1');
        $mutableOtp->setDigits(8);
        $mutableOtp->setLabel('alice@foo.bar');
        $mutableOtp->setIssuer('My Project');
        $mutableOtp->setParameter('image', 'https://foo.bar/baz');

        static::assertSame($expectedUri, $mutableOtp->getProvisioningUri());
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
        static::assertSame(1101, $otp->getCounter());
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
     * @param non-empty-string $digest
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
        return HOTP::createFromSecret($secret)
            ->withCounter($counter)
            ->withDigest($digest)
            ->withDigits($digits)
            ->withLabel($label)
            ->withIssuer($issuer);
    }
}
