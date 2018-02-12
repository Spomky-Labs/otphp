<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Http\Authentication;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\HttpUtils;

class DefaultAuthenticationFailureHandlerTest extends TestCase
{
    /**
     * @var MockObject|HttpUtils
     */
    private $httpUtils;

    /**
     * @var DefaultAuthenticationFailureHandler
     */
    private $failureHandler;

    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject|SessionInterface
     */
    private $session;

    protected function setUp()
    {
        $options = ['auth_form_path' => '/authFormPath'];
        $this->httpUtils = $this->createMock(HttpUtils::class);
        $this->failureHandler = new DefaultAuthenticationFailureHandler($this->httpUtils, $options);

        $this->session = $this->createMock(SessionInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);
    }

    /**
     * @test
     */
    public function onAuthenticationFailure_authenticationExceptionGiven_setExceptionMessageInSession()
    {
        $authenticationException = new AuthenticationException('Exception message');

        $this->session
            ->expects($this->once())
            ->method('set')
            ->with(Security::AUTHENTICATION_ERROR, $authenticationException);

        $this->failureHandler->onAuthenticationFailure($this->request, $authenticationException);
    }

    /**
     * @test
     */
    public function onAuthenticationFailure_failedAuthentication_redirectToAuthenticationForm()
    {
        $redirectResponse = $this->createMock(RedirectResponse::class);
        $this->httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($this->request, '/authFormPath')
            ->willReturn($redirectResponse);

        $returnValue = $this->failureHandler->onAuthenticationFailure($this->request, new AuthenticationException());
        $this->assertSame($redirectResponse, $returnValue);
    }
}
