<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Validation;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\EmailCodeValidator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class EmailCodeValidatorTest extends TestCase
{
    /**
     * @var EmailCodeValidator
     */
    private $validator;

    protected function setUp()
    {
        $this->validator = new EmailCodeValidator();
    }

    /**
     * @test
     * @dataProvider getCheckCodeData
     */
    public function checkCode_validateCode_returnBoolean($code, $input, $expectedReturnValue)
    {
        //Mock the user object
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->once())
            ->method('getEmailAuthCode')
            ->willReturn($code);

        $returnValue = $this->validator->checkCode($user, $input);
        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    /**
     * Test data for checkCode: code, input, result.
     *
     * @return array
     */
    public function getCheckCodeData()
    {
        return [
            [12345, 12345, true],
            [12345, 10000, false],
        ];
    }
}
