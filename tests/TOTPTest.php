<?php

declare(strict_types=1);

namespace OTPHP\Test;

use function assert;
use InvalidArgumentException;
use OTPHP\TOTP;
use OTPHP\TOTPInterface;
use ParagonIE\ConstantTime\Base32;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @internal
 */
final class TOTPTest extends TestCase
{
    /**
     * @test
     */
    public function labelNotDefined(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The label is not set.');
        $otp = TOTP::generate();
        $otp->getProvisioningUri();
    }

    /**
     * @test
     */
    public function customParameter(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');
        $otp->setPeriod(20);
        $otp->setDigest('sha512');
        $otp->setDigits(8);
        $otp->setEpoch(100);
        $otp->setLabel('alice@foo.bar');
        $otp->setIssuer('My Project');
        $otp->setParameter('foo', 'bar.baz');

        static::assertSame(
            'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&epoch=100&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y',
            $otp->getProvisioningUri()
        );
    }

    /**
     * @test
     */
    public function objectCreationValid(): void
    {
        $otp = TOTP::generate();

        static::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $otp->getSecret());
    }

    /**
     * @test
     */
    public function periodIsNot1OrMore(): void
    {
        $totp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Period must be at least 1.');
        $totp->setPeriod(-20);
    }

    /**
     * @test
     */
    public function epochIsNot0OrMore(): void
    {
        $totp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Epoch must be greater than or equal to 0.');
        $totp->setEpoch(-1);
    }

    /**
     * @test
     */
    public function secretShouldBeBase32Encoded(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to decode the secret. Is it correctly base32 encoded?');
        $secret = random_bytes(32);

        $otp = TOTP::createFromSecret($secret);
        $otp->now();
    }

    /**
     * @test
     */
    public function getProvisioningUri(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertSame(
            'otpauth://totp/My%20Project%3Aalice%40foo.bar?issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y',
            $otp->getProvisioningUri()
        );
    }

    /**
     * @test
     */
    public function getProvisioningUriWithNonDefaultArgSeperator(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30);

        ini_set('arg_separator.output', '&amp;');

        static::assertSame(
            'otpauth://totp/My%20Project%3Aalice%40foo.bar?issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y',
            $otp->getProvisioningUri()
        );
    }

    /**
     * @param positive-int $timestamp
     * @param positive-int $period
     * @param positive-int $expectedRemainder
     * @test
     * @dataProvider dataRemainingTimeBeforeExpiration
     */
    public function getRemainingTimeBeforeExpiration(int $timestamp, int $period, int $expectedRemainder): void
    {
        ClockMock::register(TOTP::class);
        ClockMock::withClockMock($timestamp);
        $otp = $this->createTOTP(6, 'sha1', $period);

        static::assertSame($expectedRemainder, $otp->expiresIn());
    }

    /**
     * @test
     */
    public function generateOtpAt(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertSame('855783', $otp->at(0));
        static::assertSame('762124', $otp->at(319690800));
        static::assertSame('139664', $otp->at(1301012137));
    }

    /**
     * @test
     */
    public function generateOtpWithEpochAt(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100);

        static::assertSame('855783', $otp->at(100));
        static::assertSame('762124', $otp->at(319690900));
        static::assertSame('139664', $otp->at(1301012237));
    }

    /**
     * @test
     */
    public function wrongSizeOtp(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertFalse($otp->verify('0'));
        static::assertFalse($otp->verify('00'));
        static::assertFalse($otp->verify('000'));
        static::assertFalse($otp->verify('0000'));
        static::assertFalse($otp->verify('00000'));
    }

    /**
     * @test
     */
    public function generateOtpNow(): void
    {
        ClockMock::register(TOTP::class);
        $time = time();
        ClockMock::withClockMock($time);
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertSame($otp->now(), $otp->at($time));
    }

    /**
     * @test
     */
    public function verifyOtpNow(): void
    {
        ClockMock::register(TOTP::class);
        $time = time();
        ClockMock::withClockMock($time);
        $otp = $this->createTOTP(6, 'sha1', 30);

        $totp = $otp->at($time);
        static::assertTrue($otp->verify($totp, $time));
    }

    /**
     * @test
     */
    public function verifyOtp(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertTrue($otp->verify('855783', 0));
        static::assertTrue($otp->verify('762124', 319690800));
        static::assertTrue($otp->verify('139664', 1301012137));

        static::assertFalse($otp->verify('139664', 1301012107));
        static::assertFalse($otp->verify('139664', 1301012167));
        static::assertFalse($otp->verify('139664', 1301012197));
    }

    /**
     * @test
     */
    public function verifyOtpWithEpoch(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100);

        static::assertTrue($otp->verify('855783', 100));
        static::assertTrue($otp->verify('762124', 319690900));
        static::assertTrue($otp->verify('139664', 1301012237));

        static::assertFalse($otp->verify('139664', 1301012207));
        static::assertFalse($otp->verify('139664', 1301012267));
        static::assertFalse($otp->verify('139664', 1301012297));
    }

    /**
     * @test
     */
    public function notCompatibleWithGoogleAuthenticator(): void
    {
        $otp = $this->createTOTP(9, 'sha512', 10);

        static::assertSame(
            'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=9&issuer=My%20Project&period=10&secret=JDDK4U6G3BJLEZ7Y',
            $otp->getProvisioningUri()
        );
    }

    /**
     * @dataProvider dataVectors
     *
     * @param TOTPInterface $totp
     * @param positive-int      $timestamp
     * @param non-empty-string  $expected_value
     *
     * @test
     */
    public function vectors($totp, $timestamp, $expected_value): void
    {
        static::assertSame($expected_value, $totp->at($timestamp));
        static::assertTrue($totp->verify($expected_value, $timestamp));
    }

    /**
     * @see https://tools.ietf.org/html/rfc6238#appendix-B
     * @see http://www.rfc-editor.org/errata_search.php?rfc=6238
     *
     * @return array<int, mixed[]>
     */
    public function dataVectors(): array
    {
        $sha1key = Base32::encodeUpper('12345678901234567890');
        assert($sha1key !== '');
        $totp_sha1 = $this->createTOTP(8, 'sha1', 30, $sha1key);
        $sha256key = Base32::encodeUpper('12345678901234567890123456789012');
        assert($sha256key !== '');
        $totp_sha256 = $this->createTOTP(8, 'sha256', 30, $sha256key);
        $sha512key = Base32::encodeUpper('1234567890123456789012345678901234567890123456789012345678901234');
        assert($sha512key !== '');
        $totp_sha512 = $this->createTOTP(8, 'sha512', 30, $sha512key);

        return [
            [$totp_sha1, 59, '94287082'],
            [$totp_sha256, 59, '46119246'],
            [$totp_sha512, 59, '90693936'],
            [$totp_sha1, 1111111109, '07081804'],
            [$totp_sha256, 1111111109, '68084774'],
            [$totp_sha512, 1111111109, '25091201'],
            [$totp_sha1, 1111111111, '14050471'],
            [$totp_sha256, 1111111111, '67062674'],
            [$totp_sha512, 1111111111, '99943326'],
            [$totp_sha1, 1234567890, '89005924'],
            [$totp_sha256, 1234567890, '91819424'],
            [$totp_sha512, 1234567890, '93441116'],
            [$totp_sha1, 2000000000, '69279037'],
            [$totp_sha256, 2000000000, '90698825'],
            [$totp_sha512, 2000000000, '38618901'],
            [$totp_sha1, 20000000000, '65353130'],
            [$totp_sha256, 20000000000, '77737706'],
            [$totp_sha512, 20000000000, '47863826'],
        ];
    }

    /**
     * @test
     */
    public function invalidOtpWindow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The leeway must be lower than the TOTP period');
        $otp = $this->createTOTP(6, 'sha1', 30);
        $otp->verify('123456', null, 31);
    }

    /**
     * @param positive-int $timestamp
     * @param non-empty-string $input
     * @param 0|positive-int $leeway
     * @test
     * @dataProvider dataLeeway
     */
    public function verifyOtpInWindow(int $timestamp, string $input, int $leeway, bool $expectedResult): void
    {
        ClockMock::register(TOTP::class);
        ClockMock::withClockMock($timestamp);
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertSame($expectedResult, $otp->verify($input, null, $leeway));
    }

    /**
     * @param positive-int $timestamp
     * @param non-empty-string $input
     * @param 0|positive-int $leeway
     * @test
     * @dataProvider dataLeewayWithEpoch
     */
    public function verifyOtpWithEpochInWindow(
        int $timestamp,
        string $input,
        int $leeway,
        bool $expectedResult
    ): void {
        ClockMock::register(TOTP::class);
        ClockMock::withClockMock($timestamp);
        $otp = $this->createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100);

        static::assertSame($expectedResult, $otp->verify($input, null, $leeway));
    }

    /**
     * @return array<int, int|string|bool>[]
     */
    public function dataLeewayWithEpoch(): array
    {
        return [
            [319690889, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
            [319690890, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319690899, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319690899, '762124', 0, false], //No leeway, **out** the period
            [319690900, '762124', 0, true], //No leeway, in the period
            [319690920, '762124', 0, true], //No leeway, in the period
            [319690929, '762124', 0, true], //No leeway, in the period
            [319690930, '762124', 0, false], //No leeway, **out** the period
            [319690930, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319690939, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319690940, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
        ];
    }

    /**
     * @test
     */
    public function qRCodeUri(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30, 'DJBSWY3DPEHPK3PXP', 'alice@google.com', 'My Big Compagny');

        static::assertSame(
            'http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%253Aalice%2540google.com%3Fissuer%3DMy%2520Big%2520Compagny%26secret%3DDJBSWY3DPEHPK3PXP',
            $otp->getQrCodeUri(
                'http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl={PROVISIONING_URI}',
                '{PROVISIONING_URI}'
            )
        );
        static::assertSame(
            'http://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%253Aalice%2540google.com%3Fissuer%3DMy%2520Big%2520Compagny%26secret%3DDJBSWY3DPEHPK3PXP&qzone=2&margin=0&size=300x300&ecc=H',
            $otp->getQrCodeUri(
                'http://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=[DATA HERE]&qzone=2&margin=0&size=300x300&ecc=H',
                '[DATA HERE]'
            )
        );
    }

    /**
     * @return int[][]
     */
    public function dataRemainingTimeBeforeExpiration(): array
    {
        return [
            [1644926810, 90, 40],
            [1644926810, 30, 10],
            [1644926810, 20, 10],
            [1577833199, 90, 1],
            [1577833199, 30, 1],
            [1577833199, 20, 1],
            [1577833200, 90, 90],
            [1577833200, 30, 30],
            [1577833200, 20, 20],
            [1577833201, 90, 89],
            [1577833201, 30, 29],
            [1577833201, 20, 19],
        ];
    }

    /**
     * @return array<int, int|string|bool>[]
     */
    public function dataLeeway(): array
    {
        return [
            [319690789, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
            [319690790, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319690799, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319690799, '762124', 0, false], //No leeway, **out** the period
            [319690800, '762124', 0, true], //No leeway, in the period
            [319690820, '762124', 0, true], //No leeway, in the period
            [319690829, '762124', 0, true], //No leeway, in the period
            [319690830, '762124', 0, false], //No leeway, **out** the period
            [319690830, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319690839, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319690840, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
        ];
    }

    /**
     * @param positive-int $digits
     * @param non-empty-string $digest
     * @param positive-int $period
     * @param non-empty-string $secret
     * @param non-empty-string $label
     * @param non-empty-string $issuer
     * @param 0|positive-int $epoch
     */
    private function createTOTP(
        int $digits,
        string $digest,
        int $period,
        string $secret = 'JDDK4U6G3BJLEZ7Y',
        string $label = 'alice@foo.bar',
        string $issuer = 'My Project',
        int $epoch = 0
    ): TOTP {
        $otp = TOTP::createFromSecret($secret);
        $otp->setPeriod($period);
        $otp->setDigest($digest);
        $otp->setDigits($digits);
        $otp->setEpoch($epoch);
        $otp->setLabel($label);
        $otp->setIssuer($issuer);

        return $otp;
    }
}
