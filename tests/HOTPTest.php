<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

class HOTPTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProvisioningUri()
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        $this->assertEquals('otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
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
