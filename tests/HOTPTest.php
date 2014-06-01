<?php

namespace OTPHP;

class HOTPTest extends \PHPUnit_Framework_TestCase
{

    public function testGetProvisioningUri()
    {
        $otp = $this->getMockBuilder('OTPHP\HOTP')
            ->setMethods(array('getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getInitialCount'))
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

        $this->assertEquals('otpauth://hotp/My%20Project%3Aalice%40foo.bar?algorithm=sha1&counter=1000&digits=8&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }
}
