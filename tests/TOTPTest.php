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
use function assert;

/**
 * @internal
 */
final class TOTPTest extends TestCase
{
    #[Test]
    public function labelNotDefined(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The label is not set. Either label or issuer must be set.');
        $otp = TOTP::generate(new InternalClock());
        $otp->getProvisioningUri();
    }

    #[Test]
    public function provisioningUriWithIssuerOnly(): void
    {
        $otp = TOTP::generate(new InternalClock());
        $otp->setIssuer('My Issuer');

        $uri = $otp->getProvisioningUri();

        static::assertStringContainsString('otpauth://totp/My%20Issuer?', $uri);
        static::assertStringContainsString('issuer=My%20Issuer', $uri);
    }

    #[Test]
    public function provisioningUriWithLabelOnly(): void
    {
        $otp = TOTP::generate(new InternalClock());
        $otp->setLabel('alice@foo.bar');

        $uri = $otp->getProvisioningUri();

        static::assertStringContainsString('otpauth://totp/alice%40foo.bar?', $uri);
        static::assertStringNotContainsString('issuer=', $uri);
    }

    #[Test]
    public function provisioningUriWithIssuerAndLabel(): void
    {
        $otp = TOTP::generate(new InternalClock());
        $otp->setIssuer('My Project');
        $otp->setLabel('alice@foo.bar');

        $uri = $otp->getProvisioningUri();

        static::assertStringContainsString('otpauth://totp/My%20Project%3Aalice%40foo.bar?', $uri);
        static::assertStringContainsString('issuer=My%20Project', $uri);
    }

    #[Test]
    public function customParameter(): void
    {
        $readonlyOtp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock())
            ->withPeriod(20)
            ->withDigest('sha512')
            ->withDigits(8)
            ->withEpoch(100)
            ->withLabel('alice@foo.bar')
            ->withIssuer('My Project')
            ->withParameter('foo', 'bar.baz')
        ;

        $expectedUri = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&epoch=100&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';

        static::assertSame($expectedUri, $readonlyOtp->getProvisioningUri());

        $mutableOtp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $mutableOtp->setPeriod(20);
        $mutableOtp->setDigest('sha512');
        $mutableOtp->setDigits(8);
        $mutableOtp->setEpoch(100);
        $mutableOtp->setLabel('alice@foo.bar');
        $mutableOtp->setIssuer('My Project');
        $mutableOtp->setParameter('foo', 'bar.baz');

        static::assertSame($expectedUri, $mutableOtp->getProvisioningUri());
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
     */
    #[Test]
    #[DataProvider('dataRemainingTimeBeforeExpiration')]
    public function getRemainingTimeBeforeExpiration(
        int $timestamp,
        int $period,
        int $epoch,
        int $expectedRemainder
    ): void {
        $clock = new ClockMock();
        $clock->setDateTime(DateTimeImmutable::createFromFormat('U', (string) $timestamp));
        $otp = self::createTOTP(6, 'sha1', $period, epoch: $epoch, clock: $clock);

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
     * @param positive-int      $timestamp
     * @param non-empty-string $expected_value
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
     *  @return iterable<int, mixed[]>
     */
    public static function dataVectors(): iterable
    {
        $sha1key = Base32::encodeUpper('12345678901234567890');
        assert($sha1key !== '');
        $totp_sha1 = self::createTOTP(8, 'sha1', 30, $sha1key);
        $sha256key = Base32::encodeUpper('12345678901234567890123456789012');
        assert($sha256key !== '');
        $totp_sha256 = self::createTOTP(8, 'sha256', 30, $sha256key);
        $sha512key = Base32::encodeUpper('1234567890123456789012345678901234567890123456789012345678901234');
        assert($sha512key !== '');
        $totp_sha512 = self::createTOTP(8, 'sha512', 30, $sha512key);
        yield [$totp_sha1, 59, '94287082'];
        yield [$totp_sha256, 59, '46119246'];
        yield [$totp_sha512, 59, '90693936'];
        yield [$totp_sha1, 1_111_111_109, '07081804'];
        yield [$totp_sha256, 1_111_111_109, '68084774'];
        yield [$totp_sha512, 1_111_111_109, '25091201'];
        yield [$totp_sha1, 1_111_111_111, '14050471'];
        yield [$totp_sha256, 1_111_111_111, '67062674'];
        yield [$totp_sha512, 1_111_111_111, '99943326'];
        yield [$totp_sha1, 1_234_567_890, '89005924'];
        yield [$totp_sha256, 1_234_567_890, '91819424'];
        yield [$totp_sha512, 1_234_567_890, '93441116'];
        yield [$totp_sha1, 2_000_000_000, '69279037'];
        yield [$totp_sha256, 2_000_000_000, '90698825'];
        yield [$totp_sha512, 2_000_000_000, '38618901'];
        yield [$totp_sha1, 20_000_000_000, '65353130'];
        yield [$totp_sha256, 20_000_000_000, '77737706'];
        yield [$totp_sha512, 20_000_000_000, '47863826'];
    }

    #[Test]
    public function invalidOtpWindow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The leeway must be lower than the TOTP period');
        $otp = self::createTOTP(6, 'sha1', 30);
        $otp->verify('123456', null, 31);
    }

    /**
     * @param positive-int $timestamp
     * @param non-empty-string $input
     * @param positive-int $leeway
     */
    #[Test]
    #[DataProvider('dataLeeway')]
    public function verifyOtpInWindow(int $timestamp, string $input, int $leeway, bool $expectedResult): void
    {
        $clock = new ClockMock();
        $clock->setDateTime(DateTimeImmutable::createFromFormat('U', (string) $timestamp));
        $otp = self::createTOTP(6, 'sha1', 30, clock: $clock);

        static::assertSame($expectedResult, $otp->verify($input, null, $leeway));
    }

    /**
     * @param positive-int $timestamp
     * @param non-empty-string $input
     * @param positive-int $leeway
     */
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
     * @return iterable<array-key, int|string|bool>[]
     */
    public static function dataLeewayWithEpoch(): iterable
    {
        yield 'Leeway of 10 seconds, **out** the period of 11sec (11 second before)' => [
            319_690_889,
            '762124',
            10,
            false,
        ];
        yield 'Leeway of 10 seconds, **out** the period of 10sec (10 second before)' => [
            319_690_890,
            '762124',
            10,
            true,
        ];
        yield 'Leeway of 10 seconds, **out** the period (1 second before)' => [319_690_899, '762124', 10, true];
        yield 'No leeway, **out** the period (1 second before)' => [319_690_899, '762124', 0, false];
        yield 'No leeway, in the period (start)' => [319_690_900, '762124', 0, true];
        yield 'No leeway, in the period (middle)' => [319_690_920, '762124', 0, true];
        yield 'No leeway, in the period (end)' => [319_690_929, '762124', 0, true];
        yield 'No leeway, **out** the period (1 second after)' => [319_690_930, '762124', 0, false];
        yield 'Leeway of 10 seconds, **out** the period (1 second after)' => [319_690_930, '762124', 10, true];
        yield 'Leeway of 10 seconds, **out** the period of 10sec (10 second after)' => [
            319_690_939,
            '762124',
            10,
            true,
        ];
        yield 'Leeway of 10 seconds, **out** the period of 11sec (11 second after)' => [
            319_690_940,
            '762124',
            10,
            false,
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
    public static function dataRemainingTimeBeforeExpiration(): iterable
    {
        yield [1_644_926_810, 90, 0, 40];
        yield [1_644_926_810, 30, 0, 10];
        yield [1_644_926_810, 20, 0, 10];
        yield [1_577_833_199, 90, 0, 1];
        yield [1_577_833_199, 30, 0, 1];
        yield [1_577_833_199, 20, 0, 1];
        yield [1_577_833_200, 90, 0, 90];
        yield [1_577_833_200, 30, 0, 30];
        yield [1_577_833_200, 20, 0, 20];
        yield [1_577_833_201, 90, 0, 89];
        yield [1_577_833_201, 30, 0, 29];
        yield [1_577_833_201, 20, 0, 19];

        yield [1_644_926_810, 90, 10, 50];
        yield [1_644_926_810, 30, 10, 20];
        yield [1_644_926_810, 20, 5, 15];
        yield [1_577_833_199, 90, 20, 21];
        yield [1_577_833_199, 30, 20, 21];
        yield [1_577_833_199, 20, 20, 1];
        yield [1_577_833_200, 90, 20, 20];
        yield [1_577_833_200, 30, 20, 20];
        yield [1_577_833_200, 20, 10, 10];
        yield [1_577_833_201, 90, 10, 9];
        yield [1_577_833_201, 30, 10, 9];
        yield [1_577_833_201, 20, 10, 9];

        yield [1_740_566_879, 100, 1_740_566_879, 100];
        yield [1_577_833_199, 200, 1_577_833_199, 200];
        yield [1_577_833_201, 300, 1_577_833_201, 300];
    }

    /**
     * @return iterable<int, int|string|bool>[]
     */
    public static function dataLeeway(): iterable
    {
        yield 'Leeway of 10 seconds, **out** the period of 11sec (11 second before)' => [
            319_690_789,
            '762124',
            10,
            false,
        ];
        yield 'Leeway of 10 seconds, **out** the period of 10sec (10 second before)' => [
            319_690_790,
            '762124',
            10,
            true,
        ];
        yield 'Leeway of 10 seconds, **out** the period of 1sec (1 second before)' => [319_690_799, '762124', 10, true];
        yield 'No leeway, **out** the period (1 second before)' => [319_690_799, '762124', 0, false];
        yield 'No leeway, in the period (start)' => [319_690_800, '762124', 0, true];
        yield 'No leeway, in the period (middle)' => [319_690_820, '762124', 0, true];
        yield 'No leeway, in the period (end)' => [319_690_829, '762124', 0, true];
        yield 'No leeway, **out** the period (1 second after)' => [319_690_830, '762124', 0, false];
        yield 'Leeway of 10 seconds, **out** the period of 1sec (1 second after)' => [319_690_830, '762124', 10, true];
        yield 'Leeway of 10 seconds, **out** the period of 10sec (10 second after)' => [
            319_690_839,
            '762124',
            10,
            true,
        ];
        yield 'Leeway of 10 seconds, **out** the period of 11sec (11 second after)' => [
            319_690_840,
            '762124',
            10,
            false,
        ];
    }

    #[Test]
    public function generateWithDefaultSecretSize(): void
    {
        $otp = TOTP::generate(new InternalClock());

        static::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $otp->getSecret());
        // Default secret size is 64 bytes, which encodes to ceil(64 * 8 / 5) = 103 base32 chars
        static::assertSame(103, strlen($otp->getSecret()));
    }

    #[Test]
    public function generateWithCustomSecretSize(): void
    {
        $otp = TOTP::generate(new InternalClock(), 16);

        static::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $otp->getSecret());
        // 16 bytes encodes to ceil(16 * 8 / 5) = 26 base32 chars
        static::assertSame(26, strlen($otp->getSecret()));
    }

    #[Test]
    public function generateWithCustomSecretSizeViaCreate(): void
    {
        $clock = new InternalClock();
        $otp = TOTP::create(clock: $clock, secretSize: 32);

        static::assertMatchesRegularExpression('/^[A-Z2-7]+$/', $otp->getSecret());
        // 32 bytes encodes to ceil(32 * 8 / 5) = 52 base32 chars
        static::assertSame(52, strlen($otp->getSecret()));
    }

    #[Test]
    public function generateWithInvalidSecretSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Secret size must be at least 1.');
        TOTP::generate(new InternalClock(), 0);
    }

    #[Test]
    public function generateWithNegativeSecretSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Secret size must be at least 1.');
        TOTP::generate(new InternalClock(), -10);
    }

    #[Test]
    public function withSecretMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withSecret('NEWSECRETBASE32A');

        static::assertSame('NEWSECRETBASE32A', $newOtp->getSecret());
        static::assertSame('JDDK4U6G3BJLEZ7Y', $otp->getSecret()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withDigitsMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withDigits(8);

        static::assertSame(8, $newOtp->getDigits());
        static::assertSame(6, $otp->getDigits()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withDigestMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withDigest('sha256');

        static::assertSame('sha256', $newOtp->getDigest());
        static::assertSame('sha1', $otp->getDigest()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withLabelMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withLabel('alice@example.com');

        static::assertSame('alice@example.com', $newOtp->getLabel());
        static::assertNull($otp->getLabel()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withIssuerMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withIssuer('My Service');

        static::assertSame('My Service', $newOtp->getIssuer());
        static::assertNull($otp->getIssuer()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withIssuerIncludedAsParameterMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $otp = $otp->withIssuer('My Service'); // Need issuer first
        $newOtp = $otp->withIssuerIncludedAsParameter(false);

        static::assertFalse($newOtp->isIssuerIncludedAsParameter());
        static::assertTrue($otp->isIssuerIncludedAsParameter()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withParameterMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withParameter('foo', 'bar');

        static::assertSame('bar', $newOtp->getParameter('foo'));
        static::assertFalse($otp->hasParameter('foo')); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withPeriodMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withPeriod(60);

        static::assertSame(60, $newOtp->getPeriod());
        static::assertSame(30, $otp->getPeriod()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    #[Test]
    public function withEpochMethodReturnsNewInstance(): void
    {
        $otp = TOTP::createFromSecret('JDDK4U6G3BJLEZ7Y', new InternalClock());
        $newOtp = $otp->withEpoch(1000);

        static::assertSame(1000, $newOtp->getEpoch());
        static::assertSame(0, $otp->getEpoch()); // Original unchanged
        static::assertNotSame($otp, $newOtp);
    }

    /**
     * @param non-empty-string $digest
     * @param non-empty-string $secret
     * @param non-empty-string $label
     * @param non-empty-string $issuer
     */
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

        return TOTP::createFromSecret($secret, $clock)
            ->withPeriod($period)
            ->withDigest($digest)
            ->withDigits($digits)
            ->withEpoch($epoch)
            ->withLabel($label)
            ->withIssuer($issuer)
        ;
    }
}
