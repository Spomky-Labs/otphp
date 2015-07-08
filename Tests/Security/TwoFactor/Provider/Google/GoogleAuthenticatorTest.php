<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class GoogleAuthenticatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $google;

    public function setUp()
    {
        $this->google = $this->getMockBuilder("Google\Authenticator\GoogleAuthenticator")
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string|null $issuer
     * @return \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator
     */
    private function createAuthenticator($issuer = null)
    {
        return new GoogleAuthenticator($this->google, "Hostname", $issuer);
    }

    /**
     * @test
     * @dataProvider getCheckCodeData
     */
    public function checkCode_validateCode_returnBoolean($code, $expectedReturnValue)
    {
        //Mock the user object
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface");
        $user
            ->expects($this->once())
            ->method("getGoogleAuthenticatorSecret")
            ->will($this->returnValue("SECRET"));

        //Mock the Google class
        $this->google
            ->expects($this->once())
            ->method("checkCode")
            ->with("SECRET", $code)
            ->will($this->returnValue($expectedReturnValue));

        $authenticator = $this->createAuthenticator();
        $returnValue = $authenticator->checkCode($user, $code);
        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    /**
     * Test data for checkCode: code, input, result
     * @return array
     */
    public function getCheckCodeData()
    {
        return array(
            array("validCode", true),
            array("invalidCode", false),
        );
    }

    /**
     * @test
     */
    public function getUrl_createQrCodeUrl_returnUrl()
    {
        //Mock the user object
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface");
        $user
            ->expects($this->once())
            ->method("getUsername")
            ->will($this->returnValue("Username"));
        $user
            ->expects($this->once())
            ->method("getGoogleAuthenticatorSecret")
            ->will($this->returnValue("SECRET"));

        $authenticator = $this->createAuthenticator();
        $returnValue = $authenticator->getUrl($user);
        $expectedUrl = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FUsername%40Hostname%3Fsecret%3DSECRET';
        $this->assertEquals($expectedUrl, $returnValue);
    }

    /**
     * @test
     */
    public function getUrl_createQrCodeUrlWithIssuer_returnUrl()
    {
        //Mock the user object
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface");
        $user
            ->expects($this->once())
            ->method("getUsername")
            ->will($this->returnValue("Username"));
        $user
            ->expects($this->once())
            ->method("getGoogleAuthenticatorSecret")
            ->will($this->returnValue("SECRET"));

        $authenticator = $this->createAuthenticator('Issuer');
        $returnValue = $authenticator->getUrl($user);
        $expectedUrl = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FIssuer%3AUsername%40Hostname%3Fsecret%3DSECRET%26issuer%3DIssuer';
        $this->assertEquals($expectedUrl, $returnValue);
    }

    /**
     * @test
     */
    public function generateSecret()
    {
        //Mock the Google class
        $this->google
            ->expects($this->once())
            ->method("generateSecret")
            ->will($this->returnValue("SECRETCODE"));

        $authenticator = $this->createAuthenticator();
        $returnValue = $authenticator->generateSecret();
        $this->assertEquals("SECRETCODE", $returnValue);
    }

}
