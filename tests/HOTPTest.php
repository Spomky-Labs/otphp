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

use Assert\Assertion;
use InvalidArgumentException;
use OTPHP\HOTP;
use PHPUnit\Framework\TestCase;

final class HOTPTest extends TestCase
{
    /**
     * @test
     */
    public function labelNotDefined(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The label is not set.');
        $hotp = HOTP::create();
        $hotp->getProvisioningUri();
    }

    /**
     * @test
     */
    public function issuerHasColon(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Issuer must not contain a colon.');
        $otp = HOTP::create('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('alice');
        $otp->setIssuer('foo%3Abar');
    }

    /**
     * @test
     */
    public function issuerHasColon2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Issuer must not contain a colon.');
        $otp = HOTP::create('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('alice');
        $otp->setIssuer('foo%3abar');
    }

    /**
     * @test
     */
    public function labelHasColon(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Label must not contain a colon.');
        $otp = HOTP::create('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('foo%3Abar');
        $otp->getProvisioningUri();
    }

    /**
     * @test
     */
    public function labelHasColon2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Label must not contain a colon.');
        $otp = HOTP::create('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 8);
        $otp->setLabel('foo:bar');
        $otp->getProvisioningUri();
    }

    /**
     * @test
     */
    public function digitsIsNot1OrMore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Digits must be at least 1.');
        HOTP::create('JDDK4U6G3BJLEZ7Y', 0, 'sha512', 0);
    }

    /**
     * @test
     */
    public function counterIsNot1OrMore(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Counter must be at least 0.');
        HOTP::create('JDDK4U6G3BJLEZ7Y', -500);
    }

    /**
     * @test
     */
    public function digestIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "foo" digest is not supported.');
        HOTP::create('JDDK4U6G3BJLEZ7Y', 0, 'foo');
    }

    /**xpectedExceptionMessage
     *
     * @test
     */
    public function secretShouldBeBase32Encoded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to decode the secret. Is it correctly base32 encoded?');
        $secret = random_bytes(32);

        $otp = HOTP::create($secret);
        $otp->at(0);
    }

    /**
     * @test
     */
    public function objectCreationValid(): void
    {
        $otp = HOTP::create();

        static::assertRegExp('/^[A-Z2-7]+$/', $otp->getSecret());
    }

    /**
     * @test
     */
    public function getProvisioningUri(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);
        $otp->setParameter('image', 'https://foo.bar/baz');

        static::assertEquals('otpauth://hotp/My%20Project%3Aalice%40foo.bar?counter=1000&digits=8&image=https%3A%2F%2Ffoo.bar%2Fbaz&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y', $otp->getProvisioningUri());
    }

    /**
     * @test
     */
    public function verifyCounterInvalid(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        static::assertFalse($otp->verify('98449994', 100));
    }

    /**
     * @test
     */
    public function verifyCounterChanged(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1100);

        static::assertTrue($otp->verify('98449994'));
        static::assertFalse($otp->verify('11111111', 1099));
        static::assertEquals($otp->getCounter(), 1101);
    }

    /**
     * @test
     */
    public function verifyValidInWindow(): void
    {
        $otp = $this->createHOTP(8, 'sha1', 1000);

        static::assertTrue($otp->verify('59647237', 1000, 50));
        static::assertFalse($otp->verify('59647237', 1000, 50));
        static::assertFalse($otp->verify('59647237', 2000, 50));
    }

    private function createHOTP(int $digits, string $digest, int $counter, string $secret = 'JDDK4U6G3BJLEZ7Y', string $label = 'alice@foo.bar', string $issuer = 'My Project'): HOTP
    {
        $otp = HOTP::create($secret, $counter, $digest, $digits);
        $otp->setLabel($label);
        $otp->setIssuer($issuer);
        Assertion::isInstanceOf($otp, HOTP::class);

        return $otp;
    }
}
