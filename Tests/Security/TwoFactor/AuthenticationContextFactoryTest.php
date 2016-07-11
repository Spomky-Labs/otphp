<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextFactory;

class AuthenticationContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $token;

    /**
     * @var AuthenticationContextFactory
     */
    private $authenticationContextFactory;

    public function setUp()
    {
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->authenticationContextFactory = new AuthenticationContextFactory('Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext');
    }

    /**
     * @test
     */
    public function create_onCreate_returnAuthenticationContext()
    {
        $this->assertInstanceOf(
            'Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext',
            $this->authenticationContextFactory->create($this->request, $this->token)
        );
    }
}
