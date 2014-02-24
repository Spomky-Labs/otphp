<?php

use OTPHP\HOTP;

class HOTPTest extends PHPUnit_Framework_TestCase
{
     /**
     * @dataProvider testProvisioningURIData
     */
    public function testProvisioningURI($secret, $name, $counter, $issuer, $expectedResult)
    {
        $hotp = new HOTP($secret);

        $this->assertEquals($expectedResult,
            $hotp->provisioningURI($name, $counter, $issuer));
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
                null,
                "otpauth://hotp/name?counter=0&algorithm=sha1&digits=6&secret=JDDK4U6G3BJLEZ7Y",
            ),
            array(
                '123456',
                'test@foo.bar',
                10,
                null,
                "otpauth://hotp/test%40foo.bar?counter=10&algorithm=sha1&digits=6&secret=123456",
            ),
            array(
                '123456',
                'test@foo.bar',
                10,
                "My Big Compagny",
                "otpauth://hotp/My%20Big%20Compagny%3Atest%40foo.bar?counter=10&issuer=My%20Big%20Compagny&algorithm=sha1&digits=6&secret=123456",
            ),
        );
    }
}
