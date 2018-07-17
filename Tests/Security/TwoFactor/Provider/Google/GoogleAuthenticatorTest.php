<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class GoogleAuthenticatorTest extends TestCase
{
    /**
     * @param string|null $hostname
     * @param string|null $issuer
     *
     * @return GoogleAuthenticator
     */
    private function createAuthenticator(?string $hostname = null, ?string $issuer = null)
    {
        return new GoogleAuthenticator($hostname, $issuer);
    }

    /**
     * @test
     * @dataProvider getHostnameAndIssuerToTest
     */
    public function getUrl_createQrCodeUrl_returnUrl(?string $hostname, ?string $issuer, string $expectedUrl)
    {
        //Mock the user object
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->once())
            ->method('getGoogleAuthenticatorUsername')
            ->willReturn('User name');
        $user
            ->expects($this->once())
            ->method('getGoogleAuthenticatorSecret')
            ->willReturn('SECRET');

        $authenticator = $this->createAuthenticator($hostname, $issuer);
        $returnValue = $authenticator->getUrl($user);
        $this->assertEquals($expectedUrl, $returnValue);
    }

    public function getHostnameAndIssuerToTest()
    {
        return [
            [null, null, 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FUser%2520name%3Fsecret%3DSECRET'],
            ['Hostname', null, 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FUser%2520name%2540Hostname%3Fsecret%3DSECRET'],
            [null, 'Issuer Name', 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FIssuer%2520Name%253AUser%2520name%3Fissuer%3DIssuer%2520Name%26secret%3DSECRET'],
            ['Hostname', 'Issuer Name', 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FIssuer%2520Name%253AUser%2520name%2540Hostname%3Fissuer%3DIssuer%2520Name%26secret%3DSECRET'],
        ];
    }
}
