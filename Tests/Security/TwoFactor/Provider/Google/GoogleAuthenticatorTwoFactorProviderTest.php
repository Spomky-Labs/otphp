<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorTwoFactorProvider;
use Scheb\TwoFactorBundle\Tests\TestCase;

class GoogleAuthenticatorTwoFactorProviderTest extends TestCase
{
    /**
     * @var MockObject|GoogleAuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var GoogleAuthenticatorTwoFactorProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->authenticator = $this->createMock(GoogleAuthenticatorInterface::class);
        $this->provider = new GoogleAuthenticatorTwoFactorProvider($this->authenticator);
    }

    /**
     * @param string $secret
     *
     * @return MockObject|TwoFactorInterface
     */
    private function createUser($secret = 'SECRET')
    {
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->any())
            ->method('getGoogleAuthenticatorSecret')
            ->willReturn($secret);

        return $user;
    }

    /**
     * @param MockObject $user
     *
     * @return MockObject|AuthenticationContextInterface
     */
    private function createAuthenticationContext($user = null)
    {
        $authContext = $this->createMock(AuthenticationContextInterface::class);
        $authContext
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user ? $user : $this->createUser());

        return $authContext;
    }

    /**
     * @test
     */
    public function beginAuthentication_twoFactorPossible_returnTrue()
    {
        $user = $this->createUser(true);
        $context = $this->createAuthenticationContext($user);

        $returnValue = $this->provider->beginAuthentication($context);
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_twoFactorDisabled_returnFalse()
    {
        $user = $this->createUser(false);
        $context = $this->createAuthenticationContext($user);

        $returnValue = $this->provider->beginAuthentication($context);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function beginAuthentication_interfaceNotImplemented_returnFalse()
    {
        $user = new \stdClass(); //Any class without TwoFactorInterface
        $context = $this->createAuthenticationContext($user);

        $returnValue = $this->provider->beginAuthentication($context);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function validateAuthenticationCode_noTwoFactorUser_returnFalse()
    {
        $user = new \stdClass();

        $this->authenticator
            ->expects($this->never())
            ->method($this->anything());

        $returnValue = $this->provider->validateAuthenticationCode($user, 'code');
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider provideValidationResult
     */
    public function validateAuthenticationCode_codeGiven_returnValidationResult($validationResult)
    {
        $user = $this->createUser();

        $this->authenticator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'code')
            ->willReturn($validationResult);

        $returnValue = $this->provider->validateAuthenticationCode($user, 'code');
        $this->assertEquals($validationResult, $returnValue);
    }

    public function provideValidationResult(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
