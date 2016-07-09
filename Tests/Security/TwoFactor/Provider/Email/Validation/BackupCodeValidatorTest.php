<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Validation;

use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\BackupCodeValidator;

class BackupCodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $backupCodeValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratedValidator;

    /**
     * @var BackupCodeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->backupCodeValidator = $this->getMockBuilder('Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeValidator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->decoratedValidator = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface');

        $this->validator = new BackupCodeValidator($this->backupCodeValidator, $this->decoratedValidator);
    }

    /**
     * @test
     */
    public function checkCode_validBackupCode_notCallDecoratedValidator()
    {
        $user = $this->createMock('Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Validation\TestableUserClass');

        //Expect backup code validator to be called
        $this->backupCodeValidator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->will($this->returnValue(true));

        //Expect decorated validator NOT to be called
        $this->decoratedValidator
            ->expects($this->never())
            ->method('checkCode');

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function checkCode_invalidBackupCode_callDecoratedValidator()
    {
        $user = $this->createMock('Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Validation\TestableUserClass');

        //Expect backup code validator to be called
        $this->backupCodeValidator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->will($this->returnValue(false));

        //Expect decorated validator to be called
        $this->decoratedValidator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->will($this->returnValue(true));

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function checkCode_backupCodesNotSupported_callDecoratedValidator()
    {
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface');

        //Expect backup code validator NOT to be called
        $this->backupCodeValidator
            ->expects($this->never())
            ->method('checkCode');

        //Expect decorated validator to be called
        $this->decoratedValidator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->will($this->returnValue(false));

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertFalse($returnValue);
    }
}

abstract class TestableUserClass implements BackupCodeInterface, TwoFactorInterface
{
}
