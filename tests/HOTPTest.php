<?php

use OTPHP\HOTP;

class HOTPTest extends PHPUnit_Framework_TestCase
{
     /**
     * @dataProvider testProvisioningURIData
     */
    public function testProvisioningURI($secret, $label, $counter, $issuer, $expectedResult, $exception = null, $message = null)
    {
        $hotp = new HOTP($secret);

        try {
            $hotp->setLabel($label);
            $hotp->setIssuer($issuer);
            $hotp->setInitialCount($counter);
            
            $this->assertEquals($expectedResult, $hotp->getProvisioningUri());

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
                "otpauth://hotp/name?algorithm=sha1&counter=0&digits=6&secret=JDDK4U6G3BJLEZ7Y",
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'test@foo.bar',
                10,
                null,
                "otpauth://hotp/test%40foo.bar?algorithm=sha1&counter=10&digits=6&secret=JDDK4U6G3BJLEZ7Y",
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'test@foo.bar',
                10,
                "My Big Compagny",
                "otpauth://hotp/My%20Big%20Compagny%3Atest%40foo.bar?algorithm=sha1&counter=10&digits=6&issuer=My%20Big%20Compagny&secret=JDDK4U6G3BJLEZ7Y",
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'test@foo.bar',
                -1,
                null,
                null,
                "Exception",
                "Initial count must be at least 0."
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                null,
                10,
                null,
                null,
                "Exception",
                "No label defined."
            ),
        );
    }
}
