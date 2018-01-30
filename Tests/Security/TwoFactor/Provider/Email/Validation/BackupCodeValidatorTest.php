<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Provider\Email\Validation;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeComparator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\BackupCodeValidator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Validation\CodeValidatorInterface;
use Scheb\TwoFactorBundle\Tests\TestCase;

class BackupCodeValidatorTest extends TestCase
{
    /**
     * @var MockObject|BackupCodeValidator
     */
    private $codeComparator;

    /**
     * @var MockObject|CodeValidatorInterface
     */
    private $decoratedValidator;

    /**
     * @var BackupCodeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->codeComparator = $this->createMock(BackupCodeComparator::class);
        $this->decoratedValidator = $this->createMock(CodeValidatorInterface::class);

        $this->validator = new BackupCodeValidator($this->codeComparator, $this->decoratedValidator);
    }

    /**
     * @test
     */
    public function checkCode_validBackupCode_notCallDecoratedValidator()
    {
        $user = $this->createMock(TestableUserClass::class);

        //Expect backup code validator to be called
        $this->codeComparator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->willReturn(true);

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
        $user = $this->createMock(TestableUserClass::class);

        //Expect backup code validator to be called
        $this->codeComparator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->willReturn(false);

        //Expect decorated validator to be called
        $this->decoratedValidator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->willReturn(true);

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function checkCode_backupCodesNotSupported_callDecoratedValidator()
    {
        $user = $this->createMock(TwoFactorInterface::class);

        //Expect backup code validator NOT to be called
        $this->codeComparator
            ->expects($this->never())
            ->method('checkCode');

        //Expect decorated validator to be called
        $this->decoratedValidator
            ->expects($this->once())
            ->method('checkCode')
            ->with($user, 'c0de')
            ->willReturn(false);

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertFalse($returnValue);
    }
}

abstract class TestableUserClass implements BackupCodeInterface, TwoFactorInterface
{
}
