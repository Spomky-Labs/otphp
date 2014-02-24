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
        $hotp = new TOTP($secret);

        $this->assertEquals($expectedOutput,$hotp->at($input));
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

    /**
     * @dataProvider testVerifyData
     */
    public function testVerify($secret, $input, $output, $expectedResult)
    {
        $hotp = new TOTP($secret);

        $this->assertEquals($expectedResult, $hotp->verify($output, $input));
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
        );
    }

    /**
     * @dataProvider testProvisioningURIData
     */
    public function testProvisioningURI($secret, $name, $expectedResult)
    {
        $hotp = new TOTP($secret);

        $this->assertEquals($expectedResult,
            $hotp->provisioningURI($name));
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
                "otpauth://totp/name?secret=JDDK4U6G3BJLEZ7Y",
            ),
        );
    }

    /**
     * @dataProvider testTimecodeData
     */
    public function testTimecode($input, $expectedOutput)
    {
        $otp = $this->getMock('OTPHP\TOTP', null, array('JDDK4U6G3BJLEZ7Y'));
        $method = self::getMethod('timecode');

        $this->assertEquals($expectedOutput, $method->invokeArgs($otp, array($input)));

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

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('OTPHP\TOTP');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
