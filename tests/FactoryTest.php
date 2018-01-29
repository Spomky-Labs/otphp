<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP\Test;

use OTPHP\Factory;
use PHPUnit\Framework\TestCase;

final class FactoryTest extends TestCase
{
    public function testTOTPLoad()
    {
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        $this->assertInstanceOf('\OTPHP\TOTP', $result);
        $this->assertEquals('My Project', $result->getIssuer());
        $this->assertEquals('alice@foo.bar', $result->getLabel());
        $this->assertEquals('sha512', $result->getDigest());
        $this->assertEquals(8, $result->getDigits());
        $this->assertEquals(20, $result->getPeriod());
        $this->assertEquals('bar.baz', $result->getParameter('foo'));
        $this->assertEquals('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        $this->assertFalse($result->hasParameter('image'));
        $this->assertTrue($result->isIssuerIncludedAsParameter());
        $this->assertEquals($otp, $result->getProvisioningUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter "image" does not exist
     */
    public function testTOTPObjectDoesNotHaveRequestedParameter()
    {
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        $result->getParameter('image');
    }

    public function testHOTPLoad()
    {
        $otp = 'otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        $this->assertInstanceOf('\OTPHP\HOTP', $result);
        $this->assertEquals('My Project', $result->getIssuer());
        $this->assertEquals('alice@foo.bar', $result->getLabel());
        $this->assertEquals('sha1', $result->getDigest());
        $this->assertEquals(8, $result->getDigits());
        $this->assertEquals(1000, $result->getCounter());
        $this->assertEquals('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        $this->assertEquals('https://foo.bar/baz', $result->getParameter('image'));
        $this->assertTrue($result->isIssuerIncludedAsParameter());
        $this->assertEquals($otp, $result->getProvisioningUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     */
    public function testBadProvisioningUri1()
    {
        $otp = 'Hello !';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     */
    public function testBadProvisioningUri2()
    {
        $otp = 'https://foo.bar/';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported "foo" OTP type
     */
    public function testBadProvisioningUri3()
    {
        $otp = 'otpauth://foo/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     */
    public function testBadProvisioningUri4()
    {
        $otp = 'otpauth://hotp:My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     */
    public function testBadProvisioningUri5()
    {
        $otp = 'bar://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid OTP: invalid issuer in parameter
     */
    public function testBadProvisioningUri6()
    {
        $otp = 'otpauth://hotp/My%20Project2%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    public function testTOTPLoadWithoutIssuer()
    {
        $otp = 'otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        $this->assertInstanceOf('\OTPHP\TOTP', $result);
        $this->assertNull($result->getIssuer());
        $this->assertEquals('My Test - Auth', $result->getLabel());
        $this->assertEquals('sha1', $result->getDigest());
        $this->assertEquals(6, $result->getDigits());
        $this->assertEquals(30, $result->getPeriod());
        $this->assertEquals('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        $this->assertFalse($result->isIssuerIncludedAsParameter());
        $this->assertEquals($otp, $result->getProvisioningUri());
    }
}
