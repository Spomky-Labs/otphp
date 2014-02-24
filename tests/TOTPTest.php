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
    public function testVerify($secret, $input, $output, $previous, $expectedResult)
    {
        $totp = new TOTP($secret);

        /*if ($expectedResult) {
            $this->assertEquals($output, $totp->at($input));
        } else {
            $this->assertNotEquals($output, $totp->at($input));
        }*/
        $this->assertEquals($expectedResult, $totp->verify($output, $input, $previous));
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
                null,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                319690800,
                762124,
                null,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012137,
                139664,
                null,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012107,
                139664,
                null,
                false,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012167,
                139664,
                1,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012197,
                139664,
                1,
                false,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1301012197,
                139664,
                2,
                true,
            ),
        );
    }

    /**
     * @dataProvider testProvisioningURIData
     */
    public function testProvisioningURI($secret, $name, $expectedResult)
    {
        $totp = new TOTP($secret);

        $this->assertEquals($expectedResult,
            $totp->provisioningURI($name));
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
            array(
                '123456',
                'test@foo.bar',
                "otpauth://totp/test%40foo.bar?secret=123456",
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

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('OTPHP\TOTP');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
