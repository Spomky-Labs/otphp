<?php

use OTPHP\TOTP;

class TOPTTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testIntervalData
     */
    public function testInterval(TOTP $totp, $expectedInterval)
    {
        $this->assertEquals($expectedInterval,$totp->getInterval());
    }

    /**
     * DataProvider of testInterval
     */
    public function testIntervalData()
    {
        return array(
            array(
                new TOTP('a'),
                30,
            ),
            array(
                new TOTP('a', 500),
                500,
            ),
            array(
                new TOTP('a', 1),
                1,
            ),
        );
    }
    
    /**
     * @dataProvider testAtData
     */
    public function testAt($secret, $input, $expectedOutput)
    {
        $totp = new TOTP($secret);

        $this->assertEquals($expectedOutput,$totp->at($input));
    }

    /**
     * DataProvider of testAt
     */
    public function testAtData()
    {
        return array(
            array(
                'JDDK4U6G3BJLEZ7Y',
                0,
                855783,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                319690800,
                762124,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012137,
                139664,
            ),
        );
    }

    public function testNow()
    {
        $totp = new TOTP('JDDK4U6G3BJLEZ7Y');
        $this->assertEquals($totp->at(time()),$totp->now());
    }

    /**
     * @dataProvider testVerifyData
     */
    public function testVerify($secret, $input, $output, $expectedResult)
    {
        $totp = new TOTP($secret);

        $this->assertEquals($expectedResult, $totp->verify($output, $input));
    }

    /**
     * DataProvider of testVerify
     */
    public function testVerifyData()
    {
        return array(
            array(
                'JDDK4U6G3BJLEZ7Y',
                0,
                855783,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                319690800,
                762124,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012137,
                139664,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012107,
                139664,
                false,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012167,
                139664,
                false,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012197,
                139664,
                false,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012197,
                139664,
                false,
            ),
        );
    }

    /**
     * @dataProvider testProvisioningURIData
     */
    public function testProvisioningURI($secret, $label, $issuer, $expectedResult)
    {
        $totp = new TOTP($secret);
        $totp->setLabel($label);
        $totp->setIssuer($issuer);

        $this->assertEquals($expectedResult, $totp->getProvisioningUri());
    }

    /**
     * DataProvider of testProvisioningURI
     */
    public function testProvisioningURIData()
    {
        return array(
            array(
                'JDDK4U6G3BJLEZ7Y',
                'name',
                null,
                "otpauth://totp/name?algorithm=sha1&digits=6&period=30&secret=JDDK4U6G3BJLEZ7Y",
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'test@foo.bar',
                null,
                "otpauth://totp/test%40foo.bar?algorithm=sha1&digits=6&period=30&secret=JDDK4U6G3BJLEZ7Y",
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'test@foo.bar',
                "My Big Compagny",
                "otpauth://totp/My%20Big%20Compagny%3Atest%40foo.bar?algorithm=sha1&digits=6&issuer=My%20Big%20Compagny&period=30&secret=JDDK4U6G3BJLEZ7Y",
            ),
        );
    }

    /**
     * @dataProvider testTimecodeData
     */
    public function testTimecode($input, $expectedOutput)
    {
        $totp = $this->getMock('OTPHP\TOTP', null, array('JDDK4U6G3BJLEZ7Y'));
        $method = self::getMethod('timecode');

        $this->assertEquals($expectedOutput, $method->invokeArgs($totp, array($input)));

    }

    /**
     * DataProvider of testTimecode
     */
    public function testTimecodeData()
    {
        return array(
            array(
                0,
                0,
            ),
            array(
                500,
                16,
            ),
            array(
                1500,
                50,
            ),
        );
    }

    /**
     * @expectedException Exception
     */
    public function testIntervalIsNotAnInteger()
    {
        $otp = new TOTP('JDDK4U6G3BJLEZ7Y');

        $otp->setInterval('inteval');
    }

    /**
     * @expectedException Exception
     */
    public function testIntervalIsNotPositive()
    {
        $otp = new TOTP('JDDK4U6G3BJLEZ7Y');

        $otp->setInterval(-1);
    }

    /**
     * @expectedException Exception
     */
    public function testIntervalIsNull()
    {
        $otp = new TOTP('JDDK4U6G3BJLEZ7Y');

        $otp->setInterval(0);
    }

    /**
     * @param string $name
     */
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('OTPHP\TOTP');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
