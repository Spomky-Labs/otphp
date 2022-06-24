<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP\Test;

use Assert\Assertion;
use InvalidArgumentException;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use PHPUnit\Framework\TestCase;

final class TOTPTest extends TestCase
{
    /**
     * @test
     */
    public function labelNotDefined(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The label is not set.');
        $hotp = TOTP::create();
        $hotp->getProvisioningUri();
        var_dump($hotp->getProvisioningUri());
    }

    /**
     * @test
     */
    public function customParameter(): void
    {
        $otp = TOTP::create('JDDK4U6G3BJLEZ7Y', 20, 'sha512', 8, 100);
        $otp->setLabel('alice@foo.bar');
        $otp->setIssuer('My Project');
        $otp->setParameter('foo', 'bar.baz');

        static::assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&epoch=100&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    /**
     * @test
     */
    public function objectCreationValid(): void
    {
        $otp = TOTP::create();

        static::assertRegExp('/^[A-Z2-7]+$/', $otp->getSecret());
    }

    /**
     * @test
     */
    public function periodIsNot1OrMore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Period must be at least 1.');
        TOTP::create('JDDK4U6G3BJLEZ7Y', -20, 'sha512', 8);
    }

    /**
     * @test
     */
    public function epochIsNot0OrMore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Epoch must be greater than or equal to 0.');
        TOTP::create('JDDK4U6G3BJLEZ7Y', 30, 'sha512', 8, -1);
    }

    /**
     * @test
     */
    public function secretShouldBeBase32Encoded(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to decode the secret. Is it correctly base32 encoded?');
        $secret = random_bytes(32);

        $otp = TOTP::create($secret);
        $otp->now();
    }

    /**
     * @test
     */
    public function getProvisioningUri(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    /**
     * @test
     */
    public function generateOtpAt(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertEquals('855783', $otp->at(0));
        static::assertEquals('762124', $otp->at(319690800));
        static::assertEquals('139664', $otp->at(1301012137));
    }

    /**
     * @test
     */
    public function generateOtpWithEpochAt(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100);

        static::assertEquals('855783', $otp->at(100));
        static::assertEquals('762124', $otp->at(319690900));
        static::assertEquals('139664', $otp->at(1301012237));
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
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertEquals($otp->now(), $otp->at(time()));
    }

    /**
     * @test
     */
    public function verifyOtpNow(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $totp = $otp->at(time());
        static::assertTrue($otp->verify($totp));
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
    public function verifyOtpWithWindowPreventsTokenReuse(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 1);

        $now = time();
        $validOtp = $otp->at($now);
        $successfulTimestamp = $otp->verifyOtpWithWindow($validOtp, $now, 0);
        static::assertEquals($now, $successfulTimestamp);

        $successfulTimestamp = $otp->verifyOtpWithWindow($validOtp, $now, 0, $successfulTimestamp);
        static::assertNull($successfulTimestamp);

        $timestampOfNextPeriod = $now + $otp->getPeriod();
        $validOtpOfNextPeriod = $otp->at($timestampOfNextPeriod);
        $successfulTimestamp = $otp->verifyOtpWithWindow($validOtpOfNextPeriod, $timestampOfNextPeriod, 0, $successfulTimestamp);
        static::assertEquals($timestampOfNextPeriod, $successfulTimestamp);
    }

    /**
     * @test
     */
    public function notCompatibleWithGoogleAuthenticator(): void
    {
        $otp = $this->createTOTP(9, 'sha512', 10);

        static::assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=9&issuer=My%20Project&period=10&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    /**
     * @dataProvider dataVectors
     *
     * @param \OTPHP\TOTPInterface $totp
     * @param int                  $timestamp
     * @param string               $expected_value
     *
     * @test
     */
    public function vectors($totp, $timestamp, $expected_value): void
    {
        static::assertEquals($expected_value, $totp->at($timestamp));
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
        $totp_sha1 = $this->createTOTP(8, 'sha1', 30, Base32::encodeUpper('12345678901234567890'));
        $totp_sha256 = $this->createTOTP(8, 'sha256', 30, Base32::encodeUpper('12345678901234567890123456789012'));
        $totp_sha512 = $this->createTOTP(8, 'sha512', 30, Base32::encodeUpper('1234567890123456789012345678901234567890123456789012345678901234'));

        return [
            [$totp_sha1,   59, '94287082'],
            [$totp_sha256, 59, '46119246'],
            [$totp_sha512, 59, '90693936'],
            [$totp_sha1,   1111111109, '07081804'],
            [$totp_sha256, 1111111109, '68084774'],
            [$totp_sha512, 1111111109, '25091201'],
            [$totp_sha1,   1111111111, '14050471'],
            [$totp_sha256, 1111111111, '67062674'],
            [$totp_sha512, 1111111111, '99943326'],
            [$totp_sha1,   1234567890, '89005924'],
            [$totp_sha256, 1234567890, '91819424'],
            [$totp_sha512, 1234567890, '93441116'],
            [$totp_sha1,   2000000000, '69279037'],
            [$totp_sha256, 2000000000, '90698825'],
            [$totp_sha512, 2000000000, '38618901'],
            [$totp_sha1,   20000000000, '65353130'],
            [$totp_sha256, 20000000000, '77737706'],
            [$totp_sha512, 20000000000, '47863826'],
        ];
    }

    /**
     * @test
     */
    public function verifyOtpInWindow(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        static::assertFalse($otp->verify('054409', 319690800, 10)); // -11 periods
        static::assertTrue($otp->verify('808167', 319690800, 10)); // -10 periods
        static::assertTrue($otp->verify('364393', 319690800, 10)); // -9 periods
        static::assertTrue($otp->verify('762124', 319690800, 10)); // 0 periods
        static::assertTrue($otp->verify('988451', 319690800, 10)); // +9 periods
        static::assertTrue($otp->verify('789387', 319690800, 10)); // +10 periods
        static::assertFalse($otp->verify('465009', 319690800, 10)); // +11 periods
    }

    /**
     * @test
     */
    public function verifyOtpWithEpochInWindow(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30, 'JDDK4U6G3BJLEZ7Y', 'alice@foo.bar', 'My Project', 100);

        static::assertFalse($otp->verify('054409', 319690900, 10)); // -11 periods
        static::assertTrue($otp->verify('808167', 319690900, 10)); // -10 periods
        static::assertTrue($otp->verify('364393', 319690900, 10)); // -9 periods
        static::assertTrue($otp->verify('762124', 319690900, 10)); // 0 periods
        static::assertTrue($otp->verify('988451', 319690900, 10)); // +9 periods
        static::assertTrue($otp->verify('789387', 319690900, 10)); // +10 periods
        static::assertFalse($otp->verify('465009', 319690900, 10)); // +11 periods
    }

    /**
     * @test
     */
    public function qRCodeUri(): void
    {
        $otp = $this->createTOTP(6, 'sha1', 30, 'DJBSWY3DPEHPK3PXP', 'alice@google.com', 'My Big Compagny');

        static::assertEquals('http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%253Aalice%2540google.com%3Fissuer%3DMy%2520Big%2520Compagny%26secret%3DDJBSWY3DPEHPK3PXP', $otp->getQrCodeUri('http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl={PROVISIONING_URI}', '{PROVISIONING_URI}'));
        static::assertEquals('http://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%253Aalice%2540google.com%3Fissuer%3DMy%2520Big%2520Compagny%26secret%3DDJBSWY3DPEHPK3PXP&qzone=2&margin=0&size=300x300&ecc=H', $otp->getQrCodeUri('http://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=[DATA HERE]&qzone=2&margin=0&size=300x300&ecc=H', '[DATA HERE]'));
    }

    private function createTOTP(int $digits, string $digest, int $period, string $secret = 'JDDK4U6G3BJLEZ7Y', string $label = 'alice@foo.bar', string $issuer = 'My Project', int $epoch = 0): TOTP
    {
        $otp = TOTP::create($secret, $period, $digest, $digits, $epoch);
        $otp->setLabel($label);
        $otp->setIssuer($issuer);
        Assertion::isInstanceOf($otp, TOTP::class);

        return $otp;
    }
}
