<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Handler;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Handler\AuthenticationHandlerInterface;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AuthenticationHandlerTestCase extends TestCase
{
    protected function getAuthenticationHandlerMock()
    {
        return $this->createMock(AuthenticationHandlerInterface::class);
    }

    protected function createAuthenticationContext($request = null, $token = null, $user = null)
    {
        $context = $this->createMock(AuthenticationContextInterface::class);
        $context
            ->expects($this->any())
            ->method('getRequest')
            ->willReturn($request ? $request : $this->createRequest());

        $context
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token ? $token : $this->createToken());

        $context
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user ? $user : $this->createUser());

        $context
            ->expects($this->any())
            ->method('getFirewallName')
            ->willReturn('firewallName');

        return $context;
    }

    protected function createRequest()
    {
        $request = $this->createMock(Request::class);

        return $request;
    }

    protected function createToken()
    {
        return $this->createMock(TokenInterface::class);
    }

    protected function createUser()
    {
        return $this->createMock(UserInterface::class);
    }

    protected function createResponse()
    {
        $response = new Response();
        $response->headers = new ResponseHeaderBag();

        return $response;
    }
}
