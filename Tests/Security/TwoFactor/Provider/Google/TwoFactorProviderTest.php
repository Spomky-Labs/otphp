<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\TwoFactorProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Renderer;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class TwoFactorProviderTest extends TestCase
{
    /**
     * @var MockObject|CodeValidatorInterface
     */
    private $authenticator;

    /**
     * @var MockObject|Renderer
     */
    private $renderer;

    /**
     * @var TwoFactorProvider
     */
    private $provider;

    public function setUp()
    {
        $this->authenticator = $this->createMock(CodeValidatorInterface::class);
        $this->renderer = $this->createMock(Renderer::class);
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
     * @return MockObject
     */
    private function getRequest()
    {
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->any())
            ->method('getUri')
            ->willReturn('/some/path');

        return $request;
    }

    /**
     * @return MockObject
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
     * @return MockObject
     */
    private function getUser($secret = 'SECRET')
    {
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->any())
            ->method('getGoogleAuthenticatorSecret')
            ->willReturn($secret);

        return $user;
    }

    /**
     * @return MockObject
     */
    private function getFlashBag()
    {
        return $this->createMock(FlashBagInterface::class);
    }

    /**
     * @param MockObject $flashBag
     *
     * @return MockObject
     */
    private function getSession($flashBag = null)
    {
        $session = $this->createMock(Session::class);
        $session
            ->expects($this->any())
            ->method('getFlashBag')
            ->willReturn($flashBag ? $flashBag : $this->getFlashBag());

        return $session;
    }

    /**
     * @param MockObject $user
     * @param MockObject $request
     * @param MockObject $session
     * @param bool       $useTrustedOption
     *
     * @return MockObject|AuthenticationContextInterface
     */
    private function getAuthenticationContext($user = null, $request = null, $session = null, $useTrustedOption = true)
    {
        $authContext = $this->createMock(AuthenticationContextInterface::class);
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
        $this->assertInstanceOf(Response::class, $returnValue);
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
        $this->assertInstanceOf(Response::class, $returnValue);
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

        /** @var RedirectResponse $returnValue */
        $returnValue = $this->provider->requestAuthenticationCode($context);
        $this->assertInstanceOf(RedirectResponse::class, $returnValue);
        $this->assertEquals('/some/path', $returnValue->getTargetUrl());
    }
}
