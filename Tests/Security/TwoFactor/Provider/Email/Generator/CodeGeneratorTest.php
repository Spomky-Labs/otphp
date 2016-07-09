<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Generator;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGenerator;

class CodeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $persister;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mailer;

    /**
     * @var TestableCodeGenerator
     */
    private $authCodeManager;

    public function setUp()
    {
        $this->persister = $this->createMock('Scheb\TwoFactorBundle\Model\PersisterInterface');

        $this->mailer = $this->createMock('Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface');

        $this->authCodeManager = new TestableCodeGenerator($this->persister, $this->mailer, 5);
        $this->authCodeManager->testCode = 12345;
    }

    /**
     * @test
     */
    public function generateAndSend_useOriginalCodeGenerator_codeBetweenRange()
    {
        //Mock the user object
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface');
        $user
            ->expects($this->once())
            ->method('setEmailAuthCode')
            ->with($this->logicalAnd(
                $this->greaterThanOrEqual(10000),
                $this->lessThanOrEqual(99999)
            ));

        //Construct test subject with original class
        $authCodeManager = new CodeGenerator($this->persister, $this->mailer, 5);
        $authCodeManager->generateAndSend($user);
    }

    /**
     * @test
     */
    public function generateAndSend_checkCodeRange_validMinAndMax()
    {
        //Stub the user object
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface');

        $this->authCodeManager->generateAndSend($user);

        //Validate min and max value
        $this->assertEquals(10000, $this->authCodeManager->lastMin);
        $this->assertEquals(99999, $this->authCodeManager->lastMax);
    }

    /**
     * @test
     */
    public function generateAndSend_generateNewCode_persistsCode()
    {
        //Mock the user object
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface');
        $user
            ->expects($this->once())
            ->method('setEmailAuthCode')
            ->with(12345);

        //Mock the persister
        $this->persister
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->authCodeManager->generateAndSend($user);
    }

    /**
     * @test
     */
    public function generateAndSend_generateNewCode_sendMail()
    {
        //Stub the user object
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface');

        //Mock the mailer
        $this->mailer
            ->expects($this->once())
            ->method('sendAuthCode')
            ->with($user);

        $this->authCodeManager->generateAndSend($user);
    }
}

//Make the AuthCodeManager class testable
class TestableCodeGenerator extends CodeGenerator
{
    public $testCode;
    public $lastMin;
    public $lastMax;

    protected function generateCode($min, $max)
    {
        $this->lastMin = $min;
        $this->lastMax = $max;

        return $this->testCode;
    }
}
