<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP\Test;

use InvalidArgumentException;
use OTPHP\Factory;
use OTPHP\HOTP;
use OTPHP\TOTP;
use PHPUnit\Framework\TestCase;

final class FactoryTest extends TestCase
{
    /**
     * @test
     */
    public function tOTPLoad(): void
    {
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        static::assertInstanceOf(TOTP::class, $result);
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
     * @test
     */
    public function tOTPObjectDoesNotHaveRequestedParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "image" does not exist');
        $otp = 'otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=8&foo=bar.baz&issuer=My%20Project&period=20&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        $result->getParameter('image');
    }

    /**
     * @test
     */
    public function hOTPLoad(): void
    {
        $otp = 'otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        static::assertInstanceOf(HOTP::class, $result);
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
     * @test
     */
    public function badProvisioningUri1(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'Hello !';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @test
     */
    public function badProvisioningUri2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'https://foo.bar/';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @test
     */
    public function badProvisioningUri3(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported "foo" OTP type');
        $otp = 'otpauth://foo/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @test
     */
    public function badProvisioningUri4(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'otpauth://hotp:My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @test
     */
    public function badProvisioningUri5(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not a valid OTP provisioning URI');
        $otp = 'bar://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @test
     */
    public function badProvisioningUri6(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid OTP: invalid issuer in parameter');
        $otp = 'otpauth://hotp/My%20Project2%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y';
        Factory::loadFromProvisioningUri($otp);
    }

    /**
     * @test
     */
    public function tOTPLoadWithoutIssuer(): void
    {
        $otp = 'otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEZ7Y';
        $result = Factory::loadFromProvisioningUri($otp);

        static::assertInstanceOf(TOTP::class, $result);
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
    public function tOTPLoadAndRemoveSecretTrailingCharacters(): void
    {
        $uri = 'otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEQ%3D%3D';
        $totp = Factory::loadFromProvisioningUri($uri);

        static::assertInstanceOf(TOTP::class, $totp);
        static::assertEquals('JDDK4U6G3BJLEQ', $totp->getSecret());
        static::assertEquals('otpauth://totp/My%20Test%20-%20Auth?secret=JDDK4U6G3BJLEQ', $totp->getProvisioningUri());
    }
}
