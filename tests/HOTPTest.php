<?php

use OTPHP\HOTP;

class HOTPTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testAtData
     */
    public function testAt($secret, $input, $expectedOutput)
    {
        $hotp = new HOTP($secret);

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
        $hotp = new HOTP($secret);

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
     * @dataProvider testProvisioningURIData
     */
    public function testProvisioningURI($secret, $name, $counter, $expectedResult)
    {
        $hotp = new HOTP($secret);

        $this->assertEquals($expectedResult,
            $hotp->provisioningURI($name, $counter));
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
                0,
                "otpauth://hotp/name?secret=JDDK4U6G3BJLEZ7Y&counter=0",
            ),
        );
    }
}
