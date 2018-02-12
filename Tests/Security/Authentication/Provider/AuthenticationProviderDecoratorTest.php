<?php

namespace Scheb\TwoFactorBundle\Tests\Security\Authentication\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\Authentication\Provider\AuthenticationProviderDecorator;
use Scheb\TwoFactorBundle\Security\Authentication\Token\TwoFactorToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactoryInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AuthenticationProviderDecoratorTest extends TestCase
{
    /**
     * @var MockObject|AuthenticationProviderInterface
     */
    private $decoratedAuthenticationProvider;

    /**
     * @var MockObject|AuthenticationHandlerInterface
     */
    private $twoFactorAuthenticationHandler;

    /**
     * @var MockObject|AuthenticationContextFactoryInterface
     */
    private $authenticationContextFactory;

    /**
     * @var MockObject|RequestStack
     */
    private $requestStack;

    /**
     * @var AuthenticationProviderDecorator
     */
    private $decorator;

    protected function setUp()
    {
        $this->decoratedAuthenticationProvider = $this->createMock(AuthenticationProviderInterface::class);
        $this->twoFactorAuthenticationHandler = $this->createMock(AuthenticationHandlerInterface::class);
        $this->authenticationContextFactory = $this->createMock(AuthenticationContextFactoryInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->requestStack
            ->expects($this->any())
            ->method('getMasterRequest')
            ->willReturn($this->createMock(Request::class));

        $this->decorator = new AuthenticationProviderDecorator(
            $this->decoratedAuthenticationProvider,
            $this->twoFactorAuthenticationHandler,
            $this->authenticationContextFactory,
            $this->requestStack,
            'firewallName'
        );
    }

    /**
     * @test
     * @dataProvider provideIgnoredToken
     */
    public function authenticate_ignoredToken_returnThatToken($ignoredToken)
    {
        $this->decoratedAuthenticationProvider
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($ignoredToken);

        $this->twoFactorAuthenticationHandler
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->decorator->authenticate($this->createMock(TokenInterface::class));
        $this->assertSame($ignoredToken, $returnValue);
    }

    public function provideIgnoredToken(): array {
        return [
            [$this->createMock(AnonymousToken::class)],
            [$this->createMock(TwoFactorToken::class)],
        ];
    }

    /**
     * @test
     */
    public function authenticate_authenticatedToken_createAuthenticationContext()
    {
        $authenticatedToken = $this->createMock(UsernamePasswordToken::class);
        $this->decoratedAuthenticationProvider
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($authenticatedToken);

        $this->authenticationContextFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(Request::class), $authenticatedToken, 'firewallName');

        $this->decorator->authenticate($this->createMock(TokenInterface::class));

    }

    /**
     * @test
     */
    public function authenticate_authenticatedToken_returnTokenFromTwoFactorAuthenticationHandler()
    {
        $authenticatedToken = $this->createMock(UsernamePasswordToken::class);
        $this->decoratedAuthenticationProvider
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn($authenticatedToken);

        $twoFactorToken = $this->createMock(TwoFactorToken::class);
        $this->twoFactorAuthenticationHandler
            ->expects($this->once())
            ->method('beginTwoFactorAuthentication')
            ->with($this->isInstanceOf(AuthenticationContextInterface::class))
            ->willReturn($twoFactorToken);

        $returnValue = $this->decorator->authenticate($this->createMock(TokenInterface::class));
        $this->assertSame($twoFactorToken, $returnValue);
    }
}
