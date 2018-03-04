<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Http\Authentication;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\HttpUtils;

class DefaultAuthenticationSuccessHandlerTest extends TestCase
{
    /**
     * @var MockObject|HttpUtils
     */
    private $httpUtils;

    /**
     * @var DefaultAuthenticationSuccessHandler
     */
    private $successHandler;

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
        $this->httpUtils = $this->createMock(HttpUtils::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->request
            ->expects($this->any())
            ->method('getSession')
            ->willReturn($this->session);
    }

    private function setUpSuccessHandlerWithOptions(bool $alwaysUseDefaultTargetPath)
    {
        $options = [
            'always_use_default_target_path' => $alwaysUseDefaultTargetPath,
            'default_target_path' => '/defaultTargetPath',
        ];
        $this->successHandler = new DefaultAuthenticationSuccessHandler($this->httpUtils, 'firewallName', $options);
    }

    private function stubSessionHasTargetPath(string $sessionTargetPath): void
    {
        $this->session
            ->expects($this->any())
            ->method('get')
            ->with('_security.firewallName.target_path')
            ->willReturn($sessionTargetPath);
    }

    private function assertCreateRedirectTo(string $targetPath): RedirectResponse
    {
        $redirectResponse = $this->createMock(RedirectResponse::class);
        $this->httpUtils
            ->expects($this->once())
            ->method('createRedirectResponse')
            ->with($this->request, $targetPath)
            ->willReturn($redirectResponse);

        return $redirectResponse;
    }

    /**
     * @test
     */
    public function onAuthenticationSuccess_hasAuthenticationException_removeAuthenticationException()
    {
        $this->setUpSuccessHandlerWithOptions(false);

        $this->session
            ->expects($this->once())
            ->method('remove')
            ->with(Security::AUTHENTICATION_ERROR);

        $token = $this->createMock(TokenInterface::class);
        $this->successHandler->onAuthenticationSuccess($this->request, $token);
    }

    /**
     * @test
     */
    public function onAuthenticationSuccess_alwaysUseDefaultTargetPath_redirectToDefaultTargetPath()
    {
        $this->setUpSuccessHandlerWithOptions(true);
        $this->stubSessionHasTargetPath('/sessionTargetPath');

        $redirectResponse = $this->assertCreateRedirectTo('/defaultTargetPath');

        $returnValue = $this->successHandler->onAuthenticationSuccess($this->request, $this->createMock(TokenInterface::class));
        $this->assertSame($redirectResponse, $returnValue);
    }

    /**
     * @test
     */
    public function onAuthenticationSuccess_hasTargetPathInSession_redirectToSessionTargetPath()
    {
        $this->setUpSuccessHandlerWithOptions(false);
        $this->stubSessionHasTargetPath('/sessionTargetPath');

        $redirectResponse = $this->assertCreateRedirectTo('/sessionTargetPath');

        $returnValue = $this->successHandler->onAuthenticationSuccess($this->request, $this->createMock(TokenInterface::class));
        $this->assertSame($redirectResponse, $returnValue);
    }

    /**
     * @test
     */
    public function onAuthenticationSuccess_noTargetPathInSession_redirectToDefaultTargetPath()
    {
        $this->setUpSuccessHandlerWithOptions(false);

        $redirectResponse = $this->assertCreateRedirectTo('/defaultTargetPath');

        $returnValue = $this->successHandler->onAuthenticationSuccess($this->request, $this->createMock(TokenInterface::class));
        $this->assertSame($redirectResponse, $returnValue);
    }
}
