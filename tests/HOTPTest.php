<?php

use OTPHP\HOTP;

class HOTPTest extends PHPUnit_Framework_TestCase
{
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
