<?php

namespace OTPHP;

class HOTPTest extends \PHPUnit_Framework_TestCase
{

    public function testGetProvisioningUri()
    {
        $otp = $this->getMockBuilder('OTPHP\HOTP')
            ->setMethods(array('getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getInitialCount', 'updateCounter'))
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

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(8));

        $otp->expects($this->any())
            ->method('getInitialCount')
            ->will($this->returnValue(1000));

        $otp->expects($this->never())
            ->method('updateCounter');

        $this->assertEquals('otpauth://hotp/My%20Project%3Aalice%40foo.bar?algorithm=sha1&counter=1000&digits=8&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testVerifyCounterInvalid()
    {
        $otp = $this->getMockBuilder('OTPHP\HOTP')
            ->setMethods(array('getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getInitialCount', 'updateCounter'))
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

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(8));

        $otp->expects($this->any())
            ->method('getInitialCount')
            ->will($this->returnValue(1000));

        $otp->expects($this->never())
            ->method('updateCounter');

        $this->assertFalse($otp->verify(0, 100));
    }

    public function testVerifyCounterChanged()
    {
        $otp = $this->getMockBuilder('OTPHP\HOTP')
            ->setMethods(array('getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getInitialCount', 'updateCounter'))
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

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(8));

        $otp->method('getInitialCount')
            ->will($this->onConsecutiveCalls(1000, 1100, 1100));

        $otp->expects($this->once())
            ->method('updateCounter')
            ->with(1101);

        $otp->verify(98449994, 1100);
        $this->assertFalse($otp->verify(111, 1099));
    }
}
