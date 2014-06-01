<?php

use OTPHP\OTPStub;
use Base32\Base32;

class OTPTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Exception
     */
    public function testLabelHasSemiColon()
    {
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');

        $otp->setLabel('my:label');
    }

    /**
     * @expectedException Exception
     */
    public function testLabelHasEncodedSemiColon()
    {
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');

        $otp->setLabel('my%3Alabel');
    }

    /**
     * @expectedException Exception
     */
    public function testLabelHasAnOtherEncodedSemiColon()
    {
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');

        $otp->setLabel('my%3alabel');
    }

    /**
     * @expectedException Exception
     */
    public function testIssuerHasSemiColon()
    {
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');

        $otp->setIssuer('my:issuer');
    }

    /**
     * @expectedException Exception
     */
    public function testIssuerHasEncodedSemiColon()
    {
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');

        $otp->setIssuer('my%3Aissuer');
    }

    /**
     * @expectedException Exception
     */
    public function testIssuerHasAnOtherEncodedSemiColon()
    {
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');

        $otp->setIssuer('my%3aissuer');
    }

    /**
     * @expectedException Exception
     */
    public function testEmptySecret()
    {
        $otp = new OTPStub('');
    }

    public function testSanitizedSecret()
    {
        $otp = new OTPStub('éç,/JDDK4U6G3.;!BJLEZ7YàÊà');

        $this->assertEquals('JDDK4U6G3BJLEZ7Y', $otp->getSecret());
    }

    /**
     * @dataProvider testAtData
     */
    public function testAt($secret, $input, $expectedOutput)
    {
        $otp = new OTPStub($secret);

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
        $otp = new OTPStub($secret);

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
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');
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
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');
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

    public function testIssuerInParameter()
    {
        $otp = new OTPStub('JDDK4U6G3BJLEZ7Y');
        $otp->setLabel('FOO');
        $otp->setIssuer('BAR');

        $this->assertEquals('otpauth://test/BAR%3AFOO?algorithm=sha1&digits=6&issuer=BAR&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());

        $otp->setIssuerIncludedAsParameter(false);
        $this->assertEquals('otpauth://test/BAR%3AFOO?algorithm=sha1&digits=6&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    /**
     * @dataProvider testGettersData
     */
    public function testGetters($secret, $digest, $digits, $issuer, $label, $exception = null, $message = null)
    {
        try {
            $otp = new OTPStub($secret,$digest, $digits, $issuer, $label);

            $this->assertEquals($secret, $otp->getSecret());
            $this->assertEquals($issuer, $otp->getIssuer());
            $this->assertEquals($label, $otp->getLabel());
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
                "My Big Compagny",
                "foo@bar.baz",
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'md5',
                8,
                "My Big Compagny",
                "foo@bar.baz",
            ),
            array(
                'abcdef',
                'foo',
                8,
                "My Big Compagny",
                "foo@bar.baz",
                'Exception',
                "'foo' digest is not supported."
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'sha1',
                2,
                "My Big Compagny",
                "foo@bar.baz",
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'sha1',
                0,
                "My Big Compagny",
                "foo@bar.baz",
                'Exception',
                "Digits must be at least 1."
            ),
            array(
                'JDDK4U6G3BJLEZ7Y',
                'sha1',
                -1,
                "My Big Compagny",
                "foo@bar.baz",
                'Exception',
                "Digits must be at least 1."
            ),
        );
    }

    /**
     * @param string $name
     */
    protected static function getMethod($name)
    {
        $class = new ReflectionClass('OTPHP\OTPStub');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
