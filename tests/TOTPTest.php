<?php

use Base32\Base32;

class TOTPTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProvisioningUri()
    {
        $otp = $this->creatTOTP(6, 'sha1', 30);

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testGenerateOtpAt()
    {
        $otp = $this->creatTOTP(6, 'sha1', 30);

        $this->assertEquals('855783', $otp->at(0));
        $this->assertEquals('762124', $otp->at(319690800));
        $this->assertEquals('139664', $otp->at(1301012137));
    }

    public function testGenerateOtpNow()
    {
        $otp = $this->creatTOTP(6, 'sha1', 30);

        $this->assertEquals($otp->now(), $otp->at(time()));
    }

    public function testVerifyOtpNow()
    {
        $otp = $this->creatTOTP(6, 'sha1', 30);

        $totp = $otp->at(time());
        $this->assertTrue($otp->verify($totp));
    }

    public function testVerifyOtp()
    {
        $otp = $this->creatTOTP(6, 'sha1', 30);

        $this->assertTrue($otp->verify('855783', 0));
        $this->assertTrue($otp->verify('762124', 319690800));
        $this->assertTrue($otp->verify('139664', 1301012137));

        $this->assertFalse($otp->verify('139664', 1301012107));
        $this->assertFalse($otp->verify('139664', 1301012167));
        $this->assertFalse($otp->verify('139664', 1301012197));
    }

    public function testNotCompatibleWithGoogleAuthenticator()
    {
        $otp = $this->creatTOTP(9, 'sha512', 10);

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
        $totp_sha1 = $this->creatTOTP(8, 'sha1',   30, Base32::encode('12345678901234567890'));
        $totp_sha256 = $this->creatTOTP(8, 'sha256', 30, Base32::encode('12345678901234567890123456789012'));
        $totp_sha512 = $this->creatTOTP(8, 'sha512', 30, Base32::encode('1234567890123456789012345678901234567890123456789012345678901234'));

        return array(
            array($totp_sha1,   59, '94287082'),
            array($totp_sha256, 59, '46119246'),
            array($totp_sha512, 59, '90693936'),
            array($totp_sha1,   1111111109, '07081804'),
            array($totp_sha256, 1111111109, '68084774'),
            array($totp_sha512, 1111111109, '25091201'),
            array($totp_sha1,   1111111111, '14050471'),
            array($totp_sha256, 1111111111, '67062674'),
            array($totp_sha512, 1111111111, '99943326'),
            array($totp_sha1,   1234567890, '89005924'),
            array($totp_sha256, 1234567890, '91819424'),
            array($totp_sha512, 1234567890, '93441116'),
            array($totp_sha1,   2000000000, '69279037'),
            array($totp_sha256, 2000000000, '90698825'),
            array($totp_sha512, 2000000000, '38618901'),
            array($totp_sha1,   20000000000, '65353130'),
            array($totp_sha256, 20000000000, '77737706'),
            array($totp_sha512, 20000000000, '47863826'),
        );
    }

    public function testWithoutGoogleAuthenticatorCompatibility()
    {
        $otp = $this->creatTOTP(6, 'sha1', 30);

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha1&digits=6&period=30&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri(false));
    }

    public function testVerifyOtpInWindow()
    {
        $otp = $this->creatTOTP(6, 'sha1', 30);

        $this->assertFalse($otp->verify('054409', 319690800, 10)); // -11 intervals
        $this->assertTrue($otp->verify('808167', 319690800, 10)); // -10 intervals
        $this->assertTrue($otp->verify('364393', 319690800, 10)); // -9 intervals
        $this->assertTrue($otp->verify('762124', 319690800, 10)); // 0 intervals
        $this->assertTrue($otp->verify('988451', 319690800, 10)); // +9 intervals
        $this->assertTrue($otp->verify('789387', 319690800, 10)); // +10 intervals
        $this->assertFalse($otp->verify('465009', 319690800, 10)); // +11 intervals
    }

    private function creatTOTP($digits, $digest, $interval, $secret = 'JDDK4U6G3BJLEZ7Y', $label = 'alice@foo.bar', $issuer = 'My Project')
    {
        $otp = $this->getMockBuilder('OTPHP\TOTP')
            ->setMethods(array('getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getInterval'))
            ->getMock();

        $otp->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue($label));

        $otp->expects($this->any())
            ->method('getSecret')
            ->will($this->returnValue($secret));

        $otp->expects($this->any())
            ->method('getIssuer')
            ->will($this->returnValue($issuer));

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue($digest));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue($digits));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue($interval));

        return $otp;
    }
}
