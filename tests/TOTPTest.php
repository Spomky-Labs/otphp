<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use Base32\Base32;
use OTPHP\TOTP;

class TOTPTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter 'foo' does not exists or is null
     */
    public function testInvalidParameter()
    {
        $otp = new TOTP();

        $otp->setLabel('alice@foo.bar')
            ->setIssuer('My Project')
            ->setDigest('sha512')
            ->setDigits(8)
            ->setIssuerIncludedAsParameter(true)
            ->setSecret('JDDK4U6G3BJLEZ7Y')
            ->setInterval(20);

        $otp->getProvisioningUri(true, ['foo']);
    }

    public function testCustomParameter()
    {
        $otp = new TOTP();

        $otp->setLabel('alice@foo.bar')
            ->setIssuer('My Project')
            ->setDigest('sha512')
            ->setDigits(8)
            ->setIssuerIncludedAsParameter(true)
            ->setSecret('JDDK4U6G3BJLEZ7Y')
            ->setInterval(20)
            ->setParameter('foo', 'bar.baz');

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri(true, ['foo']));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Interval must be at least 1.
     */
    public function testIntervalIsNotNumeric()
    {
        $otp = new TOTP();
        $otp->setInterval('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Interval must be at least 1.
     */
    public function testIntervalIsNot1OrMore()
    {
        $otp = new TOTP();
        $otp->setInterval(-500);
    }

    public function testGetProvisioningUri()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testGenerateOtpAt()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertEquals('855783', $otp->at(0));
        $this->assertEquals('762124', $otp->at(319690800));
        $this->assertEquals('139664', $otp->at(1301012137));
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

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=9&period=10&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    /**
     * @dataProvider testVectorsData
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
    public function testVectorsData()
    {
        $totp_sha1 = $this->createTOTP(8, 'sha1', 30, Base32::encode('12345678901234567890'));
        $totp_sha256 = $this->createTOTP(8, 'sha256', 30, Base32::encode('12345678901234567890123456789012'));
        $totp_sha512 = $this->createTOTP(8, 'sha512', 30, Base32::encode('1234567890123456789012345678901234567890123456789012345678901234'));

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

    public function testWithoutGoogleAuthenticatorCompatibility()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha1&digits=6&period=30&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri(false));
    }

    public function testVerifyOtpInWindow()
    {
        $otp = $this->createTOTP(6, 'sha1', 30);

        $this->assertFalse($otp->verify('054409', 319690800, 10)); // -11 intervals
        $this->assertTrue($otp->verify('808167', 319690800, 10)); // -10 intervals
        $this->assertTrue($otp->verify('364393', 319690800, 10)); // -9 intervals
        $this->assertTrue($otp->verify('762124', 319690800, 10)); // 0 intervals
        $this->assertTrue($otp->verify('988451', 319690800, 10)); // +9 intervals
        $this->assertTrue($otp->verify('789387', 319690800, 10)); // +10 intervals
        $this->assertFalse($otp->verify('465009', 319690800, 10)); // +11 intervals
    }

    private function createTOTP($digits, $digest, $interval, $secret = 'JDDK4U6G3BJLEZ7Y', $label = 'alice@foo.bar', $issuer = 'My Project')
    {
        $otp = new TOTP();
        $otp->setLabel($label)
            ->setDigest($digest)
            ->setDigits($digits)
            ->setSecret($secret)
            ->setIssuer($issuer)
            ->setIssuerIncludedAsParameter(false)
            ->setInterval($interval);

        return $otp;
    }
}
