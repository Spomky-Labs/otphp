<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactory;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticationContextFactoryTest extends TestCase
{
    /**
     * @var MockObject|Request
     */
    private $request;

    /**
     * @var MockObject|TokenInterface
     */
    private $token;

    /**
     * @var AuthenticationContextFactory
     */
    private $authenticationContextFactory;

    protected function setUp()
    {
        $this->request = $this->createMock(Request::class);
        $this->token = $this->createMock(TokenInterface::class);
        $this->authenticationContextFactory = new AuthenticationContextFactory(AuthenticationContext::class);
    }

    /**
     * @test
     */
    public function create_onCreate_returnAuthenticationContext()
    {
        $this->assertInstanceOf(
            AuthenticationContext::class,
            $this->authenticationContextFactory->create($this->request, $this->token, 'firewallName')
        );
    }
}
