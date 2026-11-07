<?php

declare(strict_types=1);

namespace OTPHP\Test;

use function ini_get;
use InvalidArgumentException;
use OTPHP\Exception\InvalidProvisioningUriException;
use OTPHP\Factory;
use OTPHP\HOTP;
use OTPHP\InternalClock;
use OTPHP\TOTP;
use const PHP_INT_MAX;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function strlen;

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
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
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
    public function aProvisioningUriWithAnEpochInTheFutureCannotMakeExpiresInThrowATypeError(): void
    {
        $uri = 'otpauth://totp/Alice?secret=JDDK4U6G3BJLEZ7Y&epoch=' . PHP_INT_MAX . '&period=' . PHP_INT_MAX;
        $totp = Factory::loadFromProvisioningUri($uri, new InternalClock());
        static::assertInstanceOf(TOTP::class, $totp);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The timestamp must be greater than or equal to the epoch.');
        $totp->expiresIn();
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
    public function verifyingAnHotpLoadedWithAnExhaustedCounterReturnsFalse(): void
    {
        // Regression test for GHSA-526c-px8p-72x8: a "counter" of PHP_INT_MAX
        // forwarded from a hostile provisioning URI made every verify() call on
        // the loaded object overflow the counter to a float and raise a
        // TypeError, which escaped the documented exceptions.
        $otp = 'otpauth://hotp/alice%40foo.bar?counter=' . PHP_INT_MAX . '&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp, new InternalClock());

        static::assertInstanceOf(HOTP::class, $result);
        static::assertFalse($result->verify('ABCDEF'));
        static::assertFalse($result->verify('ABCDEF', null, 10));
        static::assertFalse($result->verify($result->at(PHP_INT_MAX)));
        static::assertSame(PHP_INT_MAX, $result->getCounter());
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
    public function aPathLabelWithMoreThanOneLiteralColonIsRejected(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Neither issuer nor account name in label may contain a colon.');
        $uri = 'otpauth://totp/Acme:alice:bob?secret=JDDK4U6G3BJLEZ7Y';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
    }

    #[Test]
    public function aPathLabelWithMoreThanOneEncodedColonIsRejected(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Neither issuer nor account name in label may contain a colon.');
        $uri = 'otpauth://hotp/Acme%3Aalice%3abob?secret=JDDK4U6G3BJLEZ7Y&counter=0';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
    }

    #[Test]
    public function aPathLabelWithAnIssuerAndTwoEmptyPartsIsRejected(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Neither issuer nor account name in label may contain a colon.');
        $uri = 'otpauth://totp/Acme::?secret=JDDK4U6G3BJLEZ7Y';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
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
    public function aLabelQueryKeyWithAnEncodedColonAtTheStartOfTheAccountNameIsRejected(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $uri = 'otpauth://totp/Alice?secret=JDDK4U6G3BJLEZ7Y&label=Provider%3A%253Aname';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
    }

    #[Test]
    public function aPathLabelWithAnEncodedColonAtTheStartOfTheAccountNameIsRejected(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $uri = 'otpauth://totp/Provider%253A%253Aname?secret=JDDK4U6G3BJLEZ7Y';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
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

    #[Test]
    public function aProvisioningUriWithAPaddingOnlySecretIsRejected(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        // "%3D%3D" is "==": not empty for Url::fromString(), but nothing is left once the base32 padding is trimmed
        $uri = 'otpauth://totp/My%20Test%20-%20Auth?secret=%3D%3D';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
    }

    #[Test]
    public function aQueryKeyNamedParametersCannotCorruptTheInternalParametersBag(): void
    {
        $uri = 'otpauth://totp/Alice?secret=JDDK4U6G3BJLEZ7Y&parameters[foo]=bar';
        $result = Factory::loadFromProvisioningUri($uri, new InternalClock());

        static::assertInstanceOf(TOTP::class, $result);
        static::assertSame(6, $result->getDigits());
        static::assertSame(30, $result->getPeriod());
        static::assertSame('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        static::assertNotSame('', $result->getProvisioningUri());
    }

    #[Test]
    public function aQueryKeyNamedIssuerIncludedAsParameterCannotTriggerATypeError(): void
    {
        $uri = 'otpauth://totp/Alice?secret=JDDK4U6G3BJLEZ7Y&issuer_included_as_parameter=notabool';
        $result = Factory::loadFromProvisioningUri($uri, new InternalClock());

        static::assertInstanceOf(TOTP::class, $result);
        static::assertFalse($result->isIssuerIncludedAsParameter());
    }

    #[Test]
    public function aQueryKeyNamedClockCannotOverwriteTheReadonlyClockProperty(): void
    {
        $uri = 'otpauth://totp/Alice?secret=JDDK4U6G3BJLEZ7Y&clock=notaclock';
        $result = Factory::loadFromProvisioningUri($uri, new InternalClock());

        static::assertInstanceOf(TOTP::class, $result);
        static::assertSame(6, strlen($result->at(0)));
    }

    #[Test]
    public function anInvalidParameterFromTheUriSurfacesAsInvalidProvisioningUriException(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $uri = 'otpauth://totp/Alice?secret=JDDK4U6G3BJLEZ7Y&digits=0';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
    }

    #[Test]
    public function aProvisioningUriWithMoreQueryPairsThanMaxInputVarsIsRejected(): void
    {
        $this->expectException(InvalidProvisioningUriException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        // parse_str() stops after max_input_vars pairs with a mere warning: "digits=8" would be silently dropped
        $fillers = str_repeat('p=1&', (int) ini_get('max_input_vars'));
        $uri = 'otpauth://totp/Alice?secret=JDDK4U6G3BJLEZ7Y&' . $fillers . 'digits=8';

        Factory::loadFromProvisioningUri($uri, new InternalClock());
    }
}
