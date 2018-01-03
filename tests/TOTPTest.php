<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP\Test;

use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;
use PHPUnit\Framework\TestCase;

final class TOTPTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The label is not set.
     */
    public function testLabelNotDefined()
    {
        $hotp = TOTP::create();
        $this->assertTrue(is_string($hotp->now()));
        $hotp->getProvisioningUri();
    }

    public function testCustomParameter()
    {
        $otp = TOTP::create('JDDK4U6G3BJLEZ7Y', 20, 'sha512', 8);
        $otp->setLabel('alice@foo.bar');
        $otp->setIssuer('My Project');
        $otp->setParameter('foo', 'bar.baz');

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testObjectCreationValid()
    {
        $otp = TOTP::create();

        $this->assertRegExp('/^[A-Z2-7]+$/', $otp->getSecret());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Period must be at least 1.
     */
    public function testPeriodIsNot1OrMore()
    {
        TOTP::create('JDDK4U6G3BJLEZ7Y', -20, 'sha512', 8);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to decode the secret. Is it correctly base32 encoded?
     */
    public function testSecretShouldBeBase32Encoded()
    {
        $secret = random_bytes(32);

        $otp = TOTP::create($secret);
        $otp->now();
    }

    public function testGetProvisioningUri()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testGenerateOtpAt()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertEquals('855783', $otp->at(0));
        $this->assertEquals('762124', $otp->at(319690800));
        $this->assertEquals('139664', $otp->at(1301012137));
    }

    public function testWrongSizeOtp()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertFalse($otp->verify('0'));
        $this->assertFalse($otp->verify('00'));
        $this->assertFalse($otp->verify('000'));
        $this->assertFalse($otp->verify('0000'));
        $this->assertFalse($otp->verify('00000'));
    }

    public function testGenerateOtpNow()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertEquals($otp->now(), $otp->at(time()));
    }

    public function testVerifyOtpNow()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $totp = $otp->at(time());
        $this->assertTrue($otp->verify($totp));
    }

    public function testVerifyOtp()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertTrue($otp->verify('855783', 0));
        $this->assertTrue($otp->verify('762124', 319690800));
        $this->assertTrue($otp->verify('139664', 1301012137));

        $this->assertFalse($otp->verify('139664', 1301012107));
        $this->assertFalse($otp->verify('139664', 1301012167));
        $this->assertFalse($otp->verify('139664', 1301012197));
    }

    public function testNotCompatibleWithGoogleAuthenticator()
    {
        $otp = $this->createTOTP(9, 'sha512', 10);

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=9&issuer=My%20Project&period=10&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    /**
     * @dataProvider dataVectors
     *
     * @param \OTPHP\TOTPInterface $totp
     * @param int                  $timestamp
     * @param string               $expected_value
     */
    public function testVectors($totp, $timestamp, $expected_value)
    {
        $this->assertEquals($expected_value, $totp->at($timestamp));
        $this->assertTrue($totp->verify($expected_value, $timestamp));
    }

    /**
     * @see https://tools.ietf.org/html/rfc6238#appendix-B
     * @see http://www.rfc-editor.org/errata_search.php?rfc=6238
     */
    public function dataVectors()
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

    public function testVerifyOtpInWindow()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertFalse($otp->verify('054409', 319690800, 10)); // -11 periods
        $this->assertTrue($otp->verify('808167', 319690800, 10)); // -10 periods
        $this->assertTrue($otp->verify('364393', 319690800, 10)); // -9 periods
        $this->assertTrue($otp->verify('762124', 319690800, 10)); // 0 periods
        $this->assertTrue($otp->verify('988451', 319690800, 10)); // +9 periods
        $this->assertTrue($otp->verify('789387', 319690800, 10)); // +10 periods
        $this->assertFalse($otp->verify('465009', 319690800, 10)); // +11 periods
    }

    public function testQRCodeUri()
    {
        $otp = $this->createTOTP(6, 'sha1', 30, 'DJBSWY3DPEHPK3PXP', 'alice@google.com', 'My Big Compagny');

        $this->assertEquals('http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%253Aalice%2540google.com%3Fissuer%3DMy%2520Big%2520Compagny%26secret%3DDJBSWY3DPEHPK3PXP', $otp->getQrCodeUri('http://chart.apis.google.com/chart?cht=qr&chs=250x250&chl={PROVISIONING_URI}'));
        $this->assertEquals('http://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=otpauth%3A%2F%2Ftotp%2FMy%2520Big%2520Compagny%253Aalice%2540google.com%3Fissuer%3DMy%2520Big%2520Compagny%26secret%3DDJBSWY3DPEHPK3PXP&qzone=2&margin=0&size=300x300&ecc=H', $otp->getQrCodeUri('http://api.qrserver.com/v1/create-qr-code/?color=5330FF&bgcolor=70FF7E&data=[DATA HERE]&qzone=2&margin=0&size=300x300&ecc=H', '[DATA HERE]'));
    }

    private function createTOTP($digits, $digest, $period, $secret = 'JDDK4U6G3BJLEZ7Y', $label = 'alice@foo.bar', $issuer = 'My Project')
    {
        $otp = TOTP::create($secret, $period, $digest, $digits);
        $otp->setLabel($label);
        $otp->setIssuer($issuer);

        return $otp;
    }
}
