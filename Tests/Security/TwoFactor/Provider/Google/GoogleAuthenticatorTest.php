<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;

class GoogleAuthenticatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $google;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator
     */
    private $authenticator;

    public function setUp()
    {
        $this->google = $this->getMockBuilder("Google\Authenticator\GoogleAuthenticator")
            ->disableOriginalConstructor()
            ->getMock();

        $this->authenticator = new GoogleAuthenticator($this->google, "Server Name");
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

        $returnValue = $this->authenticator->checkCode($user, $code);
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

        //Mock the Google class
        $this->google
            ->expects($this->once())
            ->method("getUrl")
            ->with("Username", "Server Name", "SECRET")
            ->will($this->returnValue("http://google.com/someUrl"));

        $returnValue = $this->authenticator->getUrl($user);
        $this->assertEquals("http://google.com/someUrl", $returnValue);
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

        $returnValue = $this->authenticator->generateSecret();
        $this->assertEquals("SECRETCODE", $returnValue);
    }

}
