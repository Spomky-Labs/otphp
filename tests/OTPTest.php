<?php

class OTPTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateOtpAt()
    {
        $otp = $this->getMockBuilder('OTPHP\OTP')
            ->setMethods(array('verify', 'getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getProvisioningUri'))
            ->getMock();

        $otp->expects($this->any())
            ->method('getSecret')
            ->will($this->returnValue('JDDK4U6G3BJLEZ7Y'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(6));

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $this->assertEquals('855783', $otp->at(0));
        $this->assertEquals('549607', $otp->at(500));
        $this->assertEquals('654666', $otp->at(1500));
    }

    /**
     * @expectedException Exception
     */
    public function testGenerateUriWithoutLabel()
    {
        $otp = $this->getMockBuilder('OTPHP\OTP')
            ->getMock();

        $method = self::getMethod('generateURI');

        $method->invokeArgs($otp, array('test', array(), true));
    }

    public function testGenerateUriWithValidLabel()
    {
        $otp = $this->getMockBuilder('OTPHP\OTP')
            ->setMethods(array('verify', 'getSecret', 'getDigits', 'getDigest', 'getIssuer', 'getLabel', 'isIssuerIncludedAsParameter', 'getProvisioningUri'))
            ->getMock();

        $otp->expects($this->any())
            ->method('getLabel')
            ->will($this->returnValue('alice@foo.bar'));

        $otp->expects($this->any())
            ->method('getSecret')
            ->will($this->returnValue('JDDK4U6G3BJLEZ7Y'));

        $method = self::getMethod('generateURI');

        $this->assertEquals('otpauth://test/alice%40foo.bar?secret=JDDK4U6G3BJLEZ7Y', $method->invokeArgs($otp, array('test', array(), true)));
        $this->assertEquals('otpauth://test/alice%40foo.bar?option1=baz&secret=JDDK4U6G3BJLEZ7Y', $method->invokeArgs($otp, array('test', array('option1' => 'baz'), true)));

        $otp->expects($this->any())
            ->method('getIssuer')
            ->will($this->returnValue('My Project'));

        $otp->expects($this->any())
            ->method('getDigest')
            ->will($this->returnValue('sha1'));

        $otp->expects($this->any())
            ->method('getDigits')
            ->will($this->returnValue(8));

        $this->assertEquals('otpauth://test/My%20Project%3Aalice%40foo.bar?digits=8&secret=JDDK4U6G3BJLEZ7Y', $method->invokeArgs($otp, array('test', array(), true)));

        $otp->expects($this->any())
            ->method('isIssuerIncludedAsParameter')
            ->will($this->returnValue(true));

        $this->assertEquals('otpauth://test/My%20Project%3Aalice%40foo.bar?digits=8&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y', $method->invokeArgs($otp, array('test', array(), true)));
    }

    /**
     * @param string $name
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('OTPHP\OTP');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
