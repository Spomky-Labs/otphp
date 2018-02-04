<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\EmailTwoFactorProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Tests\TestCase;

class EmailTwoFactorProviderTest extends TestCase
{
    /**
     * @var MockObject|CodeGeneratorInterface
     */
    private $generator;

    /**
     * @var MockObject|CodeValidatorInterface
     */
    private $authenticator;

    /**
     * @var EmailTwoFactorProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->generator = $this->createMock(CodeGeneratorInterface::class);
        $this->authenticator = $this->createMock(CodeValidatorInterface::class);
        $this->provider = new EmailTwoFactorProvider($this->generator, $this->authenticator);
    }

    /**
     * @param bool $emailAuthEnabled
     *
     * @return MockObject|TwoFactorInterface
     */
    private function createUser($emailAuthEnabled = true)
    {
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->any())
            ->method('isEmailAuthEnabled')
            ->willReturn($emailAuthEnabled);

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
    public function beginAuthentication_twoFactorPossible_codeGenerated()
    {
        $user = $this->createUser(true);
        $context = $this->createAuthenticationContext($user);

        //Mock the CodeGenerator
        $this->generator
            ->expects($this->once())
            ->method('generateAndSend')
            ->with($user);

        $this->provider->beginAuthentication($context);
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
     * @dataProvider provideValidationResult
     */
    public function validateAuthenticationCode_codeGiven_returnValidationResult($validationResult)
    {
        $user = $this->createUser();
        $context = $this->createAuthenticationContext($user);

        $this->authenticator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'code')
            ->willReturn($validationResult);

        $returnValue = $this->provider->validateAuthenticationCode($context, 'code');
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
