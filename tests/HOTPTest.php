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
     * @expectedExceptionMessage Label must be a string.
     */
    public function testLabelNotDefined()
    {
        new HOTP(null, 'JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Issuer must not contain a semi-colon.
     */
    public function testIssuerHasSemicolon()
    {
        $otp = new HOTP('alice', 'JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setIssuer('foo%3Abar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Issuer must not contain a semi-colon.
     */
    public function testIssuerHasSemicolon2()
    {
        $otp = new HOTP('alice', 'JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setIssuer('foo%3abar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Label must not contain a semi-colon.
     */
    public function testLabelHasSemicolon()
    {
        new HOTP('foo%3Abar', 'JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Label must not contain a semi-colon.
     */
    public function testLabelHasSemicolon2()
    {
        new HOTP('foo:bar', 'JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Digits must be at least 1.
     */
    public function testDigitsIsNotNumeric()
    {
        new HOTP('alice', 'JDDK4U6G3BJLEZ7Y', 0, 'sha512', 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Digits must be at least 1.
     */
    public function testDigitsIsNot1OrMore()
    {
        new HOTP('alice', 'JDDK4U6G3BJLEZ7Y', 0, 'sha512', 0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Counter must be at least 0.
     */
    public function testCounterIsNotNumeric()
    {
        new HOTP('alice', 'JDDK4U6G3BJLEZ7Y', 'foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Counter must be at least 0.
     */
    public function testCounterIsNot1OrMore()
    {
        new HOTP('alice', 'JDDK4U6G3BJLEZ7Y', -500);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "foo" digest is not supported.
     */
    public function testDigestIsNotSupported()
    {
        new HOTP('alice', 'JDDK4U6G3BJLEZ7Y', 0, 'foo');
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
        $otp = $this->createHOTP(8, 'sha1', 1000);

        $this->assertTrue($otp->verify('98449994', 1100));
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
        $otp = new HOTP($label, $secret, $counter, $digest, $digits);
        $otp->setIssuer($issuer);

        return $otp;
    }
}
