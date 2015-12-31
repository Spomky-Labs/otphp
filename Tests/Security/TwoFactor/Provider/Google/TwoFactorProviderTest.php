<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\TwoFactorProvider;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $templating;

    /**
     * @var string
     */
    private $formTemplate = 'AcmeTestBundle:Test:auth.html.twig';

    /**
     * @var TwoFactorProvider
     */
    private $provider;

    public function setUp()
    {
        $this->authenticator = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\CodeValidatorInterface")
            ->disableOriginalConstructor()
            ->getMock();

        $this->templating = $this->getMock("Symfony\Bundle\FrameworkBundle\Templating\EngineInterface");

        $this->provider = new TwoFactorProvider($this->authenticator, $this->templating, $this->formTemplate, 'authCodeName');
    }

    /**
     * Stub the GoogleAuthenticator checkCode method.
     *
     * @param bool $status
     */
    private function stubGoogleAuthenticator($status)
    {
        $this->authenticator
            ->expects($this->any())
            ->method('checkCode')
            ->will($this->returnValue($status));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRequest()
    {
        $request = $this->getMockBuilder("Symfony\Component\HttpFoundation\Request")
            ->disableOriginalConstructor()
            ->getMock();
        $request
            ->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue('/some/path'));

        return $request;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPostCodeRequest($code = 12345)
    {
        $request = $this->getRequest();

        //Data
        $request
            ->expects($this->any())
            ->method('get')
            ->with('authCodeName')
            ->will($this->returnValue($code));

        return $request;
    }

    /**
     * @param bool $secret
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getUser($secret = 'SECRET')
    {
        $user = $this->getMock("Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface");
        $user
            ->expects($this->any())
            ->method('getGoogleAuthenticatorSecret')
            ->will($this->returnValue($secret));

        return $user;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFlashBag()
    {
        return $this->getMock("Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface");
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $flashBag
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSession($flashBag = null)
    {
        $session = $this->getMockBuilder("Symfony\Component\HttpFoundation\Session\Session")
            ->disableOriginalConstructor()
            ->getMock();
        $session
            ->expects($this->any())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag ? $flashBag : $this->getFlashBag()));

        return $session;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $user
     * @param \PHPUnit_Framework_MockObject_MockObject $request
     * @param \PHPUnit_Framework_MockObject_MockObject $session
     * @param bool                                     $useTrustedOption
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getAuthenticationContext($user = null, $request = null, $session = null, $useTrustedOption = true)
    {
        $authContext = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext")
            ->disableOriginalConstructor()
            ->getMock();
        $authContext
            ->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user ? $user : $this->getUser()));
        $authContext
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request ? $request : $this->getRequest()));
        $authContext
            ->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session ? $session : $this->getSession()));
        $authContext
            ->expects($this->any())
            ->method('useTrustedOption')
            ->will($this->returnValue($useTrustedOption));

        return $authContext;
    }

    /**
     * @test
     */
    public function beginAuthentication_twoFactorPossible_returnTrue()
    {
        $user = $this->getUser(true);
        $context = $this->getAuthenticationContext($user);

        $returnValue = $this->provider->beginAuthentication($context);
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_twoFactorDisabled_returnFalse()
    {
        $user = $this->getUser(false);
        $context = $this->getAuthenticationContext($user);

        $returnValue = $this->provider->beginAuthentication($context);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_interfaceNotImplemented_returnFalse()
    {
        $user = new \stdClass(); //Any class without TwoFactorInterface
        $context = $this->getAuthenticationContext($user);

        $returnValue = $this->provider->beginAuthentication($context);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider getTrustedOptions
     */
    public function requestAuthenticationCode_trustedOption_assignToTemplate($trustedOption)
    {
        //Mock the template engine
        $this->templating
            ->expects($this->once())
            ->method('renderResponse')
            ->with($this->formTemplate, array('useTrustedOption' => $trustedOption));

        $context = $this->getAuthenticationContext(null, null, null, $trustedOption);
        $this->provider->requestAuthenticationCode($context);
    }

    /**
     * Test values for trusted option in requestAuthenticationCode.
     *
     * @return array
     */
    public function getTrustedOptions()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_notPostRequest_displayForm()
    {
        $request = $this->getRequest();
        $context = $this->getAuthenticationContext(null, $request);

        //Mock the GoogleAuthenticator never called
        $this->authenticator
            ->expects($this->never())
            ->method('checkCode');

        //Mock the template engine
        $this->templating
            ->expects($this->once())
            ->method('renderResponse')
            ->will($this->returnValue(new Response('<form></form>')));

        $returnValue = $this->provider->requestAuthenticationCode($context);
        $this->assertInstanceOf("Symfony\Component\HttpFoundation\Response", $returnValue);
        $this->assertEquals('<form></form>', $returnValue->getContent());
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_postRequest_validateCode()
    {
        $user = $this->getUser();
        $request = $this->getPostCodeRequest(10000);
        $context = $this->getAuthenticationContext($user, $request);

        //Mock the GoogleAuthenticator
        $this->authenticator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 10000);

        $this->provider->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_invalidCode_displayFlashMessage()
    {
        $flashBag = $this->getFlashBag();
        $session = $this->getSession($flashBag);
        $request = $this->getPostCodeRequest();
        $context = $this->getAuthenticationContext(null, $request, $session);
        $this->stubGoogleAuthenticator(false); //Invalid code

        //Mock the session flash bag
        $flashBag
            ->expects($this->once())
            ->method('set')
            ->with('two_factor', 'scheb_two_factor.code_invalid');

        //Mock the template engine
        $this->templating
            ->expects($this->once())
            ->method('renderResponse')
            ->with($this->formTemplate, $this->anything())
            ->will($this->returnValue(new Response('<form></form>')));

        $returnValue = $this->provider->requestAuthenticationCode($context);
        $this->assertInstanceOf("Symfony\Component\HttpFoundation\Response", $returnValue);
        $this->assertEquals('<form></form>', $returnValue->getContent());
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_validCode_setAuthenticated()
    {
        $request = $this->getPostCodeRequest();
        $context = $this->getAuthenticationContext(null, $request);
        $this->stubGoogleAuthenticator(true);

        //Mock the AuthenticationContext
        $context
            ->expects($this->once())
            ->method('setAuthenticated')
            ->with(true);

        $this->provider->requestAuthenticationCode($context);
    }

    /**
     * @test
     */
    public function requestAuthenticationCode_validCode_returnRedirect()
    {
        $request = $this->getPostCodeRequest();
        $context = $this->getAuthenticationContext(null, $request);
        $this->stubGoogleAuthenticator(true);

        $returnValue = $this->provider->requestAuthenticationCode($context);
        $this->assertInstanceOf("Symfony\Component\HttpFoundation\RedirectResponse", $returnValue);
        $this->assertEquals('/some/path', $returnValue->getTargetUrl());
    }
}
