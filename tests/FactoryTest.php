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
    /**
     * @test
     */
    public function tOTPLoad()
    {
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        static::assertInstanceOf('\OTPHP\TOTP', $result);
        static::assertEquals('My Project', $result->getIssuer());
        static::assertEquals('alice@foo.bar', $result->getLabel());
        static::assertEquals('sha512', $result->getDigest());
        static::assertEquals(8, $result->getDigits());
        static::assertEquals(20, $result->getPeriod());
        static::assertEquals('bar.baz', $result->getParameter('foo'));
        static::assertEquals('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        static::assertFalse($result->hasParameter('image'));
        static::assertTrue($result->isIssuerIncludedAsParameter());
        static::assertEquals($otp, $result->getProvisioningUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter "image" does not exist
     *
     * @test
     */
    public function tOTPObjectDoesNotHaveRequestedParameter()
    {
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        $result->getParameter('image');
    }

    /**
     * @test
     */
    public function hOTPLoad()
    {
        $otp = 'otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        static::assertInstanceOf('\OTPHP\HOTP', $result);
        static::assertEquals('My Project', $result->getIssuer());
        static::assertEquals('alice@foo.bar', $result->getLabel());
        static::assertEquals('sha1', $result->getDigest());
        static::assertEquals(8, $result->getDigits());
        static::assertEquals(1000, $result->getCounter());
        static::assertEquals('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        static::assertEquals('https://foo.bar/baz', $result->getParameter('image'));
        static::assertTrue($result->isIssuerIncludedAsParameter());
        static::assertEquals($otp, $result->getProvisioningUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     *
     * @test
     */
    public function badProvisioningUri1()
    {
        $otp = 'Hello !';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     *
     * @test
     */
    public function badProvisioningUri2()
    {
        $otp = 'https://foo.bar/';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported "foo" OTP type
     *
     * @test
     */
    public function badProvisioningUri3()
    {
        $otp = 'otpauth://foo/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     *
     * @test
     */
    public function badProvisioningUri4()
    {
        $otp = 'otpauth://hotp:My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not a valid OTP provisioning URI
     *
     * @test
     */
    public function badProvisioningUri5()
    {
        $otp = 'bar://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid OTP: invalid issuer in parameter
     *
     * @test
     */
    public function badProvisioningUri6()
    {
        $otp = 'otpauth://hotp/My%20Project2%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @test
     */
    public function tOTPLoadWithoutIssuer()
    {
        $otp = 'otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        static::assertInstanceOf('\OTPHP\TOTP', $result);
        static::assertNull($result->getIssuer());
        static::assertEquals('My Test - Auth', $result->getLabel());
        static::assertEquals('sha1', $result->getDigest());
        static::assertEquals(6, $result->getDigits());
        static::assertEquals(30, $result->getPeriod());
        static::assertEquals('JDDK4U6G3BJLEZ7Y', $result->getSecret());
        static::assertFalse($result->isIssuerIncludedAsParameter());
        static::assertEquals($otp, $result->getProvisioningUri());
    }

    /**
     * @test
     */
    public function tOTPLoadAndRemoveSecretTrailingCharacters()
    {
        $uri = 'otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEQ%3D%3D';
        $totp = Factory::loadFromProvisioningUri($uri);

        static::assertInstanceOf('\OTPHP\TOTP', $totp);
        static::assertEquals('JDDK4U6G3BJLEQ', $totp->getSecret());
        static::assertEquals('otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEQ', $totp->getProvisioningUri());
    }
}
