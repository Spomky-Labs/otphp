<?php

declare(strict_types=1);

namespace OTPHP\Test;

use InvalidArgumentException;
use OTPHP\Factory;
use OTPHP\HOTP;
use OTPHP\InternalClock;
use OTPHP\TOTP;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FactoryTest extends TestCase
{
    #[Test]
    public function tOTPLoad(): void
    {
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp, new InternalClock());

        static::assertInstanceOf(TOTP::class, $result);
        static::assertSame('My Project', $result->getIssuer());
        static::assertSame('alice@foo.bar', $result->getLabel());
        static::assertSame('sha512', $result->getDigest());
        static::assertSame(8, $result->getDigits());
        static::assertSame(20, $result->getPeriod());
        static::assertSame('bar.baz', $result->getParameter('foo'));
        static::assertSame('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        static::assertFalse($result->hasParameter('image'));
        static::assertTrue($result->isIssuerIncludedAsParameter());
        static::assertSame($otp, $result->getProvisioningUri());
    }

    #[Test]
    public function loadingAProvisioningUriWithAnExcessiveDigitsParameterIsRejected(): void
    {
        // Regression test for GHSA-g7m4-839x-ch6v: an unbounded "digits" value
        // forwarded from a hostile provisioning URI used to overflow
        // "10 ** digits" and raise a DivisionByZeroError during OTP generation.
        // It must now be rejected up front with a documented exception.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Digits must be between 1 and 10.');
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?digits=50&secret=JDDK4U6G3BJLEZ7Y';

        Factory::loadFromProvisioningUri($otp, new InternalClock());
    }

    #[Test]
    public function tOTPObjectDoesNotHaveRequestedParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "image" does not exist');
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp, new InternalClock());

        $result->getParameter('image');
    }

    #[Test]
    public function hOTPLoad(): void
    {
        $otp = 'otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp, new InternalClock());

        static::assertInstanceOf(HOTP::class, $result);
        static::assertSame('My Project', $result->getIssuer());
        static::assertSame('alice@foo.bar', $result->getLabel());
        static::assertSame('sha1', $result->getDigest());
        static::assertSame(8, $result->getDigits());
        static::assertSame(1000, $result->getCounter());
        static::assertSame('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        static::assertSame('https://foo.bar/baz', $result->getParameter('image'));
        static::assertTrue($result->isIssuerIncludedAsParameter());
        static::assertSame($otp, $result->getProvisioningUri());
    }

    #[Test]
    public function badProvisioningUri1(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'Hello !';
        Factory::loadFromProvisioningUri($otp, new InternalClock());
    }

    #[Test]
    public function badProvisioningUri2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'https://foo.bar/';
        Factory::loadFromProvisioningUri($otp, new InternalClock());
    }

    #[Test]
    public function badProvisioningUri3(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported "foo" OTP type');
        $otp = 'otpauth://foo/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp, new InternalClock());
    }

    #[Test]
    public function badProvisioningUri4(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'otpauth://hotp:My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp, new InternalClock());
    }

    #[Test]
    public function badProvisioningUri5(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'bar://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp, new InternalClock());
    }

    #[Test]
    public function loadProvisioningUriWithDifferentIssuers(): void
    {
        // Test case from issue #242: Microsoft Office 365 uses different issuer in label vs parameter
        // Label: "Some Company:me@somecompany.net", Parameter: "issuer=Microsoft"
        $otp = 'otpauth://hotp/My%20Project2%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp, new InternalClock());

        static::assertInstanceOf(HOTP::class, $result);
        // Issuer parameter takes precedence
        static::assertSame('My Project', $result->getIssuer());
        static::assertSame('alice@foo.bar', $result->getLabel());
        static::assertTrue($result->isIssuerIncludedAsParameter());
    }

    #[Test]
    public function loadMicrosoftOffice365StyleUri(): void
    {
        // Real-world test case from issue #242: Microsoft Office 365 QR codes
        // Format: otpauth://totp/Some+Company:me@somecompany.net?secret=XXX&issuer=Microsoft
        $otp = 'otpauth://totp/Some+Company%3Ame%40somecompany.net?secret=JDDK4U6G3BJLEZ7Y&issuer=Microsoft';
        $result = Factory::loadFromProvisioningUri($otp, new InternalClock());

        static::assertInstanceOf(TOTP::class, $result);
        // Issuer parameter (Microsoft) takes precedence over label issuer (Some Company)
        static::assertSame('Microsoft', $result->getIssuer());
        static::assertSame('me@somecompany.net', $result->getLabel());
        static::assertTrue($result->isIssuerIncludedAsParameter());
    }

    #[Test]
    public function tOTPLoadWithoutIssuer(): void
    {
        $otp = 'otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp, new InternalClock());

        static::assertInstanceOf(TOTP::class, $result);
        static::assertNull($result->getIssuer());
        static::assertSame('My Test - Auth', $result->getLabel());
        static::assertSame('sha1', $result->getDigest());
        static::assertSame(6, $result->getDigits());
        static::assertSame(30, $result->getPeriod());
        static::assertSame('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        static::assertFalse($result->isIssuerIncludedAsParameter());
        static::assertSame($otp, $result->getProvisioningUri());
    }

    #[Test]
    public function tOTPLoadAndRemoveSecretTrailingCharacters(): void
    {
        $uri = 'otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEQ%3D%3D';
        $totp = Factory::loadFromProvisioningUri($uri, new InternalClock());

        static::assertInstanceOf(TOTP::class, $totp);
        static::assertSame('JDDK4U6G3BJLEQ', $totp->getSecret());
        static::assertSame('otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEQ', $totp->getProvisioningUri());
    }
}
