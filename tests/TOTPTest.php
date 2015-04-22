<?php

class TOTPTest extends \PHPUnit_Framework_TestCase
{
    private function getOtp()
    {
        $otp = $this->getMockBuilder('OTPHP\TOTP')
            ->setMethods(array('getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getInterval'))
            ->getMock();

        $otp->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('alice@foo.bar'));

        $otp->expects($this->any())
            ->method('getSecret')
            ->will($this->returnValue('JDDK4U6G3BJLEZ7Y'));

        $otp->expects($this->any())
            ->method('getIssuer')
            ->will($this->returnValue('My Project'));

        return $otp;
    }

    public function testGetProvisioningUri()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testGenerateOtpAt()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));

        $this->assertEquals(855783, $otp->at(0));
        $this->assertEquals(762124, $otp->at(319690800));
        $this->assertEquals(139664, $otp->at(1301012137));
    }

    public function testGenerateOtpNow()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));

        $this->assertEquals($otp->now(), $otp->at(time()));
    }

    public function testVerifyOtpNow()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));

        $totp = $otp->at(time());
        $this->assertTrue($otp->verify($totp));
    }

    public function testVerifyOtp()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));

        $this->assertTrue($otp->verify(855783, 0));
        $this->assertTrue($otp->verify(762124, 319690800));
        $this->assertTrue($otp->verify(139664, 1301012137));

        $this->assertFalse($otp->verify(139664, 1301012107));
        $this->assertFalse($otp->verify(139664, 1301012167));
        $this->assertFalse($otp->verify(139664, 1301012197));
    }

    public function testNotCompatibleWithGoogleAuthenticator()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha512'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(9));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(10));

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=9&period=10&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testWithoutGoogleAuthenticatorCompatibility()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha1&digits=6&period=30&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri(false));
    }

    public function testVerifyOtpInWindow()
    {
        $otp = $this->getOtp();

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));

        $this->assertFalse($otp->verify(54409, 319690800, 10)); // -11 intervals
        $this->assertTrue($otp->verify(808167, 319690800, 10)); // -10 intervals
        $this->assertTrue($otp->verify(364393, 319690800, 10)); // -9 intervals
        $this->assertTrue($otp->verify(762124, 319690800, 10)); // 0 intervals
        $this->assertTrue($otp->verify(988451, 319690800, 10)); // +9 intervals
        $this->assertTrue($otp->verify(789387, 319690800, 10)); // +10 intervals
        $this->assertFalse($otp->verify(465009, 319690800, 10)); // +11 intervals
    }
}
