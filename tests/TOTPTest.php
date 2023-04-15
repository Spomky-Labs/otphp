<?php

declare(strict_types=1);

namespace OTPHP\Test;

use DateTimeImmutable;
use InvalidArgumentException;
use OTPHP\InternalClock;
use OTPHP\TOTP;
use OTPHP\TOTPInterface;
use ParagonIE\ConstantTime\Base32;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use RuntimeException;

/**
 * @internal
 */
final class TOTPTest extends TestCase
{
    #[Test]
    public function labelNotDefined(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The label is not set.');
        $otp = TOTP::generate(new InternalClock());
        $otp->getProvisioningUri();
    }

    #[Test]
    public function customParameter(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
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

    #[Test]
    public function objectCreationValid(): void
    {
        $otp = TOTP::generate(new InternalClock());

        static::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $otp->getSecret());
    }

    #[Test]
    public function periodIsNot1OrMore(): void
    {
        $totp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Period must be at least 1.');
        $totp->setPeriod(-20);
    }

    #[Test]
    public function epochIsNot0OrMore(): void
    {
        $totp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Epoch must be greater than or equal to 0.');
        $totp->setEpoch(-1);
    }

    #[Test]
    public function secretShouldBeBase32Encoded(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to decode the secret. Is it correctly base32 encoded?');
        $secret = random_bytes(32);

        $otp = TOTP::createFromSecret($secret, new InternalClock());
        $otp->now();
    }

    #[Test]
    public function getProvisioningUri(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30);

        static::assertSame(
            'otpauth://totp/My%20Project%3Aalice%40foo.bar?issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y',
            $otp->getProvisioningUri()
        );
    }

    #[Test]
    #[DataProvider('dataRemainingTimeBeforeExpiration')]
    public function getRemainingTimeBeforeExpiration(int $timestamp, int $period, int $expectedRemainder): void
    {
        $clock = new ClockMock();
        $clock->setDateTime(DateTimeImmutable::createFromFormat('U', (string) $timestamp));
        $otp = self::createTOTP(6, 'sha1', $period, clock: $clock);

        static::assertSame($expectedRemainder, $otp->expiresIn());
    }

    #[Test]
    public function generateOtpAt(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30);

        static::assertSame('855783', $otp->at(0));
        static::assertSame('762124', $otp->at(319_690_800));
        static::assertSame('139664', $otp->at(1_301_012_137));
    }

    #[Test]
    public function generateOtpWithEpochAt(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100);

        static::assertSame('855783', $otp->at(100));
        static::assertSame('762124', $otp->at(319_690_900));
        static::assertSame('139664', $otp->at(1_301_012_237));
    }

    #[Test]
    public function wrongSizeOtp(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30);

        static::assertFalse($otp->verify('0'));
        static::assertFalse($otp->verify('00'));
        static::assertFalse($otp->verify('000'));
        static::assertFalse($otp->verify('0000'));
        static::assertFalse($otp->verify('00000'));
    }

    #[Test]
    public function generateOtpNow(): void
    {
        $clock = new ClockMock();
        $timestamp = time();
        $clock->setDateTime(DateTimeImmutable::createFromFormat('U', (string) $timestamp));
        $otp = self::createTOTP(6, 'sha1', 30, clock: $clock);

        static::assertSame($otp->now(), $otp->at($timestamp));
    }

    #[Test]
    public function verifyOtpNow(): void
    {
        $timestamp = time();
        $clock = new ClockMock();
        $clock->setDateTime(DateTimeImmutable::createFromFormat('U', (string) $timestamp));
        $otp = self::createTOTP(6, 'sha1', 30, clock: $clock);

        $totp = $otp->at($timestamp);
        static::assertTrue($otp->verify($totp, $timestamp));
    }

    #[Test]
    public function verifyOtp(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30);

        static::assertTrue($otp->verify('855783', 0));
        static::assertTrue($otp->verify('762124', 319_690_800));
        static::assertTrue($otp->verify('139664', 1_301_012_137));

        static::assertFalse($otp->verify('139664', 1_301_012_107));
        static::assertFalse($otp->verify('139664', 1_301_012_167));
        static::assertFalse($otp->verify('139664', 1_301_012_197));
    }

    #[Test]
    public function verifyOtpWithEpoch(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100);

        static::assertTrue($otp->verify('855783', 100));
        static::assertTrue($otp->verify('762124', 319_690_900));
        static::assertTrue($otp->verify('139664', 1_301_012_237));

        static::assertFalse($otp->verify('139664', 1_301_012_207));
        static::assertFalse($otp->verify('139664', 1_301_012_267));
        static::assertFalse($otp->verify('139664', 1_301_012_297));
    }

    #[Test]
    public function notCompatibleWithGoogleAuthenticator(): void
    {
        $otp = self::createTOTP(9, 'sha512', 10);

        static::assertSame(
            'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=9&issuer=My%20Project&period=10&secret=JDDK4U6G3BJLEZ7Y',
            $otp->getProvisioningUri()
        );
    }

    /**
     * @param TOTPInterface $totp
     * @param int           $timestamp
     * @param string        $expected_value
     */
    #[Test]
    #[DataProvider('dataVectors')]
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
    public static function dataVectors(): array
    {
        $totp_sha1 = self::createTOTP(8, 'sha1', 30, Base32::encodeUpper('12345678901234567890'));
        $totp_sha256 = self::createTOTP(8, 'sha256', 30, Base32::encodeUpper('12345678901234567890123456789012'));
        $totp_sha512 = self::createTOTP(
            8,
            'sha512',
            30,
            Base32::encodeUpper('1234567890123456789012345678901234567890123456789012345678901234')
        );

        return [
            [$totp_sha1, 59, '94287082'],
            [$totp_sha256, 59, '46119246'],
            [$totp_sha512, 59, '90693936'],
            [$totp_sha1, 1_111_111_109, '07081804'],
            [$totp_sha256, 1_111_111_109, '68084774'],
            [$totp_sha512, 1_111_111_109, '25091201'],
            [$totp_sha1, 1_111_111_111, '14050471'],
            [$totp_sha256, 1_111_111_111, '67062674'],
            [$totp_sha512, 1_111_111_111, '99943326'],
            [$totp_sha1, 1_234_567_890, '89005924'],
            [$totp_sha256, 1_234_567_890, '91819424'],
            [$totp_sha512, 1_234_567_890, '93441116'],
            [$totp_sha1, 2_000_000_000, '69279037'],
            [$totp_sha256, 2_000_000_000, '90698825'],
            [$totp_sha512, 2_000_000_000, '38618901'],
            [$totp_sha1, 20_000_000_000, '65353130'],
            [$totp_sha256, 20_000_000_000, '77737706'],
            [$totp_sha512, 20_000_000_000, '47863826'],
        ];
    }

    #[Test]
    public function invalidOtpWindow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The leeway must be lower than the TOTP period');
        $otp = self::createTOTP(6, 'sha1', 30);
        $otp->verify('123456', null, 31);
    }

    #[Test]
    #[DataProvider('dataLeeway')]
    public function verifyOtpInWindow(int $timestamp, string $input, int $leeway, bool $expectedResult): void
    {
        $clock = new ClockMock();
        $clock->setDateTime(DateTimeImmutable::createFromFormat('U', (string) $timestamp));
        $otp = self::createTOTP(6, 'sha1', 30, clock: $clock);

        static::assertSame($expectedResult, $otp->verify($input, null, $leeway));
    }

    #[Test]
    #[DataProvider('dataLeewayWithEpoch')]
    public function verifyOtpWithEpochInWindow(
        int $timestamp,
        string $input,
        int $leeway,
        bool $expectedResult
    ): void {
        $clock = new ClockMock();
        $clock->setDateTime(DateTimeImmutable::createFromFormat('U', (string) $timestamp));
        $otp = self::createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100, $clock);

        static::assertSame($expectedResult, $otp->verify($input, null, $leeway));
    }

    /**
     * @return array<int, int|string|bool>[]
     */
    public static function dataLeewayWithEpoch(): array
    {
        return [
            [319_690_889, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
            [319_690_890, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319_690_899, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319_690_899, '762124', 0, false], //No leeway, **out** the period
            [319_690_900, '762124', 0, true], //No leeway, in the period
            [319_690_920, '762124', 0, true], //No leeway, in the period
            [319_690_929, '762124', 0, true], //No leeway, in the period
            [319_690_930, '762124', 0, false], //No leeway, **out** the period
            [319_690_930, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319_690_939, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319_690_940, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
        ];
    }

    #[Test]
    public function qRCodeUri(): void
    {
        $otp = self::createTOTP(6, 'sha1', 30, 'DJBSWY3DPEHPK3PXP', 'alice@google.com', 'My Big Compagny');

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
    public static function dataRemainingTimeBeforeExpiration(): array
    {
        return [
            [1_644_926_810, 90, 40],
            [1_644_926_810, 30, 10],
            [1_644_926_810, 20, 10],
            [1_577_833_199, 90, 1],
            [1_577_833_199, 30, 1],
            [1_577_833_199, 20, 1],
            [1_577_833_200, 90, 90],
            [1_577_833_200, 30, 30],
            [1_577_833_200, 20, 20],
            [1_577_833_201, 90, 89],
            [1_577_833_201, 30, 29],
            [1_577_833_201, 20, 19],
        ];
    }

    /**
     * @return array<int, int|string|bool>[]
     */
    public static function dataLeeway(): array
    {
        return [
            [319_690_789, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
            [319_690_790, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319_690_799, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319_690_799, '762124', 0, false], //No leeway, **out** the period
            [319_690_800, '762124', 0, true], //No leeway, in the period
            [319_690_820, '762124', 0, true], //No leeway, in the period
            [319_690_829, '762124', 0, true], //No leeway, in the period
            [319_690_830, '762124', 0, false], //No leeway, **out** the period
            [319_690_830, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 1sec
            [319_690_839, '762124', 10, true], //Leeway of 10 seconds, **out** the period of 10sec
            [319_690_840, '762124', 10, false], //Leeway of 10 seconds, **out** the period of 11sec
        ];
    }

    private static function createTOTP(
        int $digits,
        string $digest,
        int $period,
        string $secret = 'JDDK4U6G3BJLEZ7Y',
        string $label = 'alice@foo.bar',
        string $issuer = 'My Project',
        int $epoch = 0,
        ?ClockInterface $clock = null
    ): TOTP {
        static::assertNotSame('', $secret);
        static::assertNotSame('', $digest);
        $clock ??= new InternalClock();

        $otp = TOTP::createFromSecret($secret, $clock);
        $otp->setPeriod($period);
        $otp->setDigest($digest);
        $otp->setDigits($digits);
        $otp->setEpoch($epoch);
        $otp->setLabel($label);
        $otp->setIssuer($issuer);

        return $otp;
    }
}
