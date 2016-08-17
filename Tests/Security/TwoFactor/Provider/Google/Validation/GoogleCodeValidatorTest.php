<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\GoogleCodeValidator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class GoogleCodeValidatorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticator;

    /**
     * @var GoogleCodeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->authenticator = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator');
        $this->validator = new GoogleCodeValidator($this->authenticator);
    }

    /**
     * @test
     */
    public function checkCode_validateCode_returnAuthenticatorResult()
    {
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface');

        $this->authenticator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->willReturn(true);

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }
}
