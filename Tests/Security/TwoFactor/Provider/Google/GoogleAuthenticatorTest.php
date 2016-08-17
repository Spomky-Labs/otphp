<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class GoogleAuthenticatorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $google;

    public function setUp()
    {
        $this->google = $this->createMock('Google\Authenticator\GoogleAuthenticator');
    }

    /**
     * @param string|null $hostname
     * @param string|null $issuer
     *
     * @return GoogleAuthenticator
     */
    private function createAuthenticator($hostname = null, $issuer = null)
    {
        return new GoogleAuthenticator($this->google, $hostname, $issuer);
    }

    /**
     * @test
     * @dataProvider getCheckCodeData
     */
    public function checkCode_validateCode_returnBoolean($code, $expectedReturnValue)
    {
        //Mock the user object
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface');
        $user
            ->expects($this->once())
            ->method('getGoogleAuthenticatorSecret')
            ->willReturn('SECRET');

        //Mock the Google class
        $this->google
            ->expects($this->once())
            ->method('checkCode')
            ->with('SECRET', $code)
            ->willReturn($expectedReturnValue);

        $authenticator = $this->createAuthenticator();
        $returnValue = $authenticator->checkCode($user, $code);
        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    /**
     * Test data for checkCode: code, input, result.
     *
     * @return array
     */
    public function getCheckCodeData()
    {
        return array(
            array('validCode', true),
            array('invalidCode', false),
        );
    }

    /**
     * @test
     * @dataProvider getHostnameAndIssuerToTest
     */
    public function getUrl_createQrCodeUrl_returnUrl($hostname, $issuer, $expectedUrl)
    {
        //Mock the user object
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface');
        $user
            ->expects($this->once())
            ->method('getUsername')
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
        return array(
            array(null, null, 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FUser%2520name%3Fsecret%3DSECRET'),
            array('Hostname', null, 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FUser%2520name%40Hostname%3Fsecret%3DSECRET'),
            array(null, 'Issuer Name', 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FIssuer%2520Name%3AUser%2520name%3Fsecret%3DSECRET%26issuer%3DIssuer%2520Name'),
            array('Hostname', 'Issuer Name', 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FIssuer%2520Name%3AUser%2520name%40Hostname%3Fsecret%3DSECRET%26issuer%3DIssuer%2520Name'),
        );
    }

    /**
     * @test
     */
    public function generateSecret()
    {
        //Mock the Google class
        $this->google
            ->expects($this->once())
            ->method('generateSecret')
            ->willReturn('SECRETCODE');

        $authenticator = $this->createAuthenticator();
        $returnValue = $authenticator->generateSecret();
        $this->assertEquals('SECRETCODE', $returnValue);
    }
}
