<?php

use OTPHP\HOTP;

class HOTPTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No label defined.
     */
    public function testLabelNotDefined()
    {
        $otp = new HOTP();

        $otp->getProvisioningUri();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Issuer must not contain a semi-colon.
     */
    public function testIssuerHasSemicolon()
    {
        $otp = new HOTP();
        $otp->setIssuer('foo%3Abar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Issuer must not contain a semi-colon.
     */
    public function testIssuerHasSemicolon2()
    {
        $otp = new HOTP();
        $otp->setIssuer('foo%3abar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Label must not contain a semi-colon.
     */
    public function testLabelHasSemicolon()
    {
        $otp = new HOTP();
        $otp->setLabel('foo:bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Digits must be at least 1.
     */
    public function testDigitsIsNotNumeric()
    {
        $otp = new HOTP();
        $otp->setDigits('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Digits must be at least 1.
     */
    public function testDigitsIsNot1OrMore()
    {
        $otp = new HOTP();
        $otp->setDigits(-500);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Counter must be at least 0.
     */
    public function testCounterIsNotNumeric()
    {
        $otp = new HOTP();
        $otp->setCounter('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Counter must be at least 0.
     */
    public function testCounterIsNot1OrMore()
    {
        $otp = new HOTP();
        $otp->setCounter(-500);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage 'foo' digest is not supported.
     */
    public function testDigestIsNotSupported()
    {
        $otp = new HOTP();
        $otp->setDigest('foo');
    }

    public function testGetProvisioningUri()
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);
        $otp->setIssuerIncludedAsParameter(true)
            ->setImage('https://foo.bar/baz');

        $this->assertEquals('otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testVerifyCounterInvalid()
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        $this->assertFalse($otp->verify(0, 100));
    }

    public function testVerifyCounterChanged()
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        $this->assertTrue($otp->verify('98449994', 1100));
        $this->assertFalse($otp->verify('11111111', 1099));
        $this->assertTrue($otp->getCounter() === 1101);
    }

    public function testVerifyValidInWindow()
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        $this->assertTrue($otp->verify('59647237', 1000, 50));
        $this->assertFalse($otp->verify('59647237', 1000, 50));
        $this->assertFalse($otp->verify('59647237', 2000, 50));
    }

    private function createHOTP($digits, $digest, $counter, $secret = 'JDDK4U6G3BJLEZ7Y', $label = 'alice@foo.bar', $issuer = 'My Project')
    {
        $otp = new \OTPHP\HOTP();
        $otp->setLabel($label)
            ->setDigest($digest)
            ->setDigits($digits)
            ->setSecret($secret)
            ->setIssuer($issuer)
            ->setCounter($counter);

        return $otp;
    }
}
