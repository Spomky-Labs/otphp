<?php

use MyProject\TOTP;
use MyProject\HOTP;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    public function testTotpExample()
    {
        $totp = new TOTP();
        $totp->setLabel('alice@foo.bar')
              ->setSecret('JDDK4U6G3BJLEZ7Y')
              ->setIssuer('My Project')
              ->setIssuerIncludedAsParameter(true)
              ->setDigest('sha512')
              ->setDigits(10)
              ->setInterval(60);

        $this->assertEquals('otpauth://totp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&digits=10&issuer=My%20Project&period=60&secret=JDDK4U6G3BJLEZ7Y', $totp->getProvisioningUri());
    }

    public function testHotpExample()
    {
        $totp = new HOTP();
        $totp->setLabel('alice@foo.bar')
              ->setSecret('JDDK4U6G3BJLEZ7Y')
              ->setIssuer('My Project')
              ->setIssuerIncludedAsParameter(true)
              ->setDigest('sha512')
              ->setDigits(10)
              ->setCounter(1000);

        $this->assertEquals('otpauth://hotp/My%20Project%3Aalice%40foo.bar?algorithm=sha512&counter=1000&digits=10&issuer=My%20Project&secret=JDDK4U6G3BJLEZ7Y', $totp->getProvisioningUri());
    }
}
