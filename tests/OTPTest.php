<?php

use OTPHP\OTP;

class OTPTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testAtData
     */
    public function testAt($secret, $input, $expectedOutput)
    {
        $otp = $this->getMock('OTPHP\OTP', null, array($secret));

        $this->assertEquals($expectedOutput,$otp->at($input));
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
                500,
                549607,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1500,
                654666,
            ),
        );
    }

    /**
     * @dataProvider testVerifyData
     */
    public function testVerify($secret, $input, $output, $expectedResult)
    {
        $otp = $this->getMock('OTPHP\OTP', null, array($secret));

        $this->assertEquals($expectedResult, $otp->verify($output, $input));
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
                500,
                549607,
                true,
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                1500,
                654666,
                true,
            ),
        );
    }

    /**
     * @dataProvider testIntToBytestringData
     */
    public function testIntToBytestring($input, $expectedOutput)
    {
        $otp = $this->getMock('OTPHP\OTP', null, array('JDDK4U6G3BJLEZ7Y'));
        $method = self::getMethod('intToBytestring');

        $this->assertEquals($expectedOutput, $method->invokeArgs($otp, array($input)));
    }

    /**
     * DataProvider of testIntToBytestring
     */
    public function testIntToBytestringData()
    {
        return array(
            array(
                0,
                "\000\000\000\000\000\000\000\000",
            ),
            array(
                1,
                "\000\000\000\000\000\000\000\001",
            ),
            array(
                500,
                "\000\000\000\000\000\000\001\364",
            ),
            array(
                1500,
                "\000\000\000\000\000\000\005\334",
            ),
        );
    }

    /**
     * @dataProvider testGenerateOTPData
     */
    public function testGenerateOTP($input, $expectedOutput)
    {
        $otp = $this->getMock('OTPHP\OTP', null, array('JDDK4U6G3BJLEZ7Y'));
        $method = self::getMethod('generateOTP');

        $this->assertEquals($expectedOutput, $method->invokeArgs($otp, array($input)));
    }

    /**
     * DataProvider of testGenerateOTP
     */
    public function testGenerateOTPData()
    {
        return array(
            array(
                0,
                855783,
            ),
            array(
                500,
                549607,
            ),
            array(
                1500,
                654666,
            ),
        );
    }

    /**
     * @dataProvider testGettersData
     */
    public function testGetters($secret, $digest, $digits, $exception = null, $message = null)
    {
        try {
            $otp = $this->getMock('OTPHP\OTP', null, array($secret,$digest, $digits));

            $this->assertEquals($secret, $otp->getSecret());
            $this->assertEquals($digest, $otp->getDigest());
            $this->assertEquals($digits, $otp->getDigits());

            if ($exception !== null) {

                $this->fail("The expected exception '$exception' was not thrown");
            }
        } catch ( \Exception $e ) {
            if (!$exception || !($e instanceof $exception)) {
                throw $e;
            }
            $this->assertEquals($message, $e->getMessage());
        }

    }

    /**
     * DataProvider of testGetters
     */
    public function testGettersData()
    {
        return array(
            array(
                'JDDK4U6G3BJLEZ7Y',
                'sha1',
                6,
            ),
            array(
                '1234567890',
                'md5',
                8,
            ),
            array(
                'abcdef',
                'foo',
                10,
                'Exception',
                "'foo' digest is not supported."
            ),
        );
    }

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('OTPHP\OTP');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
