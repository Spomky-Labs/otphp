<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use OTPHP\HOTP;

class HOTPTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Label must be null or a string.
     */
    public function testLabelNotNullAndNotAStringDefined()
    {
        $otp = HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel(123);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The label is not set.
     */
    public function testLabelNotDefined()
    {
        $hotp = HOTP::createHOTP();
        $this->assertTrue(is_string($hotp->at(0)));
        $hotp->getProvisioningUri();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Issuer must not contain a colon.
     */
    public function testIssuerHasColon()
    {
        $otp = HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('alice');
        $otp->setIssuer('foo%3Abar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Issuer must not contain a colon.
     */
    public function testIssuerHasColon2()
    {
        $otp = HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('alice');
        $otp->setIssuer('foo%3abar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Label must not contain a colon.
     */
    public function testLabelHasColon()
    {
        $otp = HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('foo%3Abar');
        $otp->getProvisioningUri();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Label must not contain a colon.
     */
    public function testLabelHasColon2()
    {
        $otp = HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('foo:bar');
        $otp->getProvisioningUri();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Digits must be at least 1.
     */
    public function testDigitsIsNotNumeric()
    {
        HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Digits must be at least 1.
     */
    public function testDigitsIsNot1OrMore()
    {
        HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Counter must be at least 0.
     */
    public function testCounterIsNotNumeric()
    {
        HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Counter must be at least 0.
     */
    public function testCounterIsNot1OrMore()
    {
        HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', -500);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "foo" digest is not supported.
     */
    public function testDigestIsNotSupported()
    {
        HOTP::createHOTP('JDDK4U6G3BJLEZ7Y', 0, 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The secret must be a string or null.
     */
    public function testSecretIsNotAString()
    {
        HOTP::createHOTP(1234);
    }

    public function testObjectCreationValid()
    {
        $otp = HOTP::createHOTP();

        $this->assertRegExp('/^[A-Z2-7]+$/', $otp->getSecret());
    }

    public function testGetProvisioningUri()
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);
        $otp->setParameter('image', 'https://foo.bar/baz');

        $this->assertEquals('otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    public function testVerifyCounterInvalid()
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        $this->assertFalse($otp->verify('98449994', 100));
    }

    public function testVerifyCounterChanged()
    {
        $otp = $this->createHOTP(8, 'sha1', 1100);

        $this->assertTrue($otp->verify('98449994'));
        $this->assertFalse($otp->verify('11111111', 1099));
        $this->assertEquals($otp->getCounter(), 1101);
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
        $otp = HOTP::createHOTP($secret, $counter, $digest, $digits);
        $otp->setLabel($label);
        $otp->setIssuer($issuer);

        return $otp;
    }
}
