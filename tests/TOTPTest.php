<?php

namespace OTPHP;

class TOTPTest extends \PHPUnit_Framework_TestCase
{
    private $otp;
    public function setUp()
    {
        $this->otp = $this->getMockBuilder('OTPHP\TOTP')
            ->setMethods(array('getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getInterval'))
            ->getMock();

        $this->otp->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('alice@foo.bar'));

        $this->otp->expects($this->any())
            ->method('getSecret')
            ->will($this->returnValue('JDDK4U6G3BJLEZ7Y'));

        $this->otp->expects($this->any())
            ->method('getIssuer')
            ->will($this->returnValue('My Project'));

        $this->otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $this->otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $this->otp->expects($this->any())
            ->method('getInterval')
            ->will($this->returnValue(30));
    }

    public function testGetProvisioningUri()
    {
        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha1&digits=6&period=30&secret=JDDK4U6G3BJLEZ7Y', $this->otp->getProvisioningUri());
    }

    public function testGenerateOtpAt()
    {
        $this->assertEquals(855783, $this->otp->at(0));
        $this->assertEquals(762124, $this->otp->at(319690800));
        $this->assertEquals(139664, $this->otp->at(1301012137));
    }

    public function testGenerateOtpNow()
    {
        $this->assertEquals($this->otp->now(), $this->otp->at(time()));
    }

    public function testVerifyOtpNow()
    {
        $totp = $this->otp->at(time());
        $this->assertTrue($this->otp->verify($totp));
    }

    public function testVerifyOtp()
    {
        $this->assertTrue($this->otp->verify(855783, 0));
        $this->assertTrue($this->otp->verify(762124, 319690800));
        $this->assertTrue($this->otp->verify(139664, 1301012137));

        $this->assertFalse($this->otp->verify(139664, 1301012107));
        $this->assertFalse($this->otp->verify(139664, 1301012167));
        $this->assertFalse($this->otp->verify(139664, 1301012197));
    }

    public function testVerifyOtpInWindow()
    {
        $this->assertFalse($this->otp->verify(54409, 319690800, 10)); // -11 intervals
        $this->assertTrue($this->otp->verify(808167, 319690800, 10)); // -10 intervals
        $this->assertTrue($this->otp->verify(364393, 319690800, 10)); // -9 intervals
        $this->assertTrue($this->otp->verify(762124, 319690800, 10)); // 0 intervals
        $this->assertTrue($this->otp->verify(988451, 319690800, 10)); // +9 intervals
        $this->assertTrue($this->otp->verify(789387, 319690800, 10)); // +10 intervals
        $this->assertFalse($this->otp->verify(465009, 319690800, 10)); // +11 intervals
    }
}
