<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google\Validation;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\GoogleCodeValidator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class GoogleCodeValidatorTest extends TestCase
{
    /**
     * @var MockObject|GoogleAuthenticator
     */
    private $authenticator;

    /**
     * @var GoogleCodeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->authenticator = $this->createMock(GoogleAuthenticator::class);
        $this->validator = new GoogleCodeValidator($this->authenticator);
    }

    /**
     * @test
     */
    public function checkCode_validateCode_returnAuthenticatorResult()
    {
        $user = $this->createMock(TwoFactorInterface::class);

        $this->authenticator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->willReturn(true);

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }
}
