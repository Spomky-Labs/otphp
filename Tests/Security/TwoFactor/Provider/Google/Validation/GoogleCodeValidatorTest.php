<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Google\Validation;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\Validation\GoogleCodeValidator;

class GoogleCodeValidatorTest extends \PHPUnit_Framework_TestCase
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
        $this->authenticator = $this->getMockBuilder('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator')
            ->disableOriginalConstructor()
            ->getMock();

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
            ->will($this->returnValue(true));

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }
}
