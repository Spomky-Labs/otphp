<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\TwoFactorProvider;
use Symfony\Component\HttpFoundation\Response;
use Scheb\TwoFactorBundle\Tests\TestCase;

class TwoFactorProviderTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $renderer;

    /**
     * @var TwoFactorProvider
     */
    private $provider;

    public function setUp()
    {
        $this->authenticator = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\CodeValidatorInterface');
        $this->renderer = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\Renderer');
        $this->provider = new TwoFactorProvider($this->authenticator, $this->renderer, 'authCodeName');
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
            ->willReturn($status);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRequest()
    {
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('/some/path');

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
            ->willReturn($code);

        return $request;
    }

    /**
     * @param string $secret
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getUser($secret = 'SECRET')
    {
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface');
        $user
            ->expects($this->any())
            ->method('getGoogleAuthenticatorSecret')
            ->willReturn($secret);

        return $user;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getFlashBag()
    {
        return $this->createMock('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface');
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $flashBag
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSession($flashBag = null)
    {
        $session = $this->createMock('Symfony\Component\HttpFoundation\Session\Session');
        $session
            ->expects($this->any())
            ->method('getFlashBag')
            ->willReturn($flashBag ? $flashBag : $this->getFlashBag());

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
        $authContext = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface');
        $authContext
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user ? $user : $this->getUser());
        $authContext
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($request ? $request : $this->getRequest());
        $authContext
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($session ? $session : $this->getSession());
        $authContext
            ->expects($this->any())
            ->method('useTrustedOption')
            ->willReturn($useTrustedOption);

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
        $context = $this->getAuthenticationContext(null, null, null, $trustedOption);

        //Mock the template engine
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with($context);

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
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->willReturn(new Response('<form></form>'));

        $returnValue = $this->provider->requestAuthenticationCode($context);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $returnValue);
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
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with($context)
            ->willReturn(new Response('<form></form>'));

        $returnValue = $this->provider->requestAuthenticationCode($context);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $returnValue);
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
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $returnValue);
        $this->assertEquals('/some/path', $returnValue->getTargetUrl());
    }
}
