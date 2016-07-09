<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Validation;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\EmailCodeValidator;

class EmailCodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailCodeValidator
     */
    private $validator;

    public function setUp()
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
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface');
        $user
            ->expects($this->once())
            ->method('getEmailAuthCode')
            ->will($this->returnValue($code));

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
        return array(
            array(12345, 12345, true),
            array(12345, 10000, false),
        );
    }
}
