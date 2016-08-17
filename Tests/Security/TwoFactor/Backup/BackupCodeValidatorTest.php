<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Backup;

use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeValidator;
use Scheb\TwoFactorBundle\Tests\TestCase;

class BackupCodeValidatorTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $persister;

    /**
     * @var BackupCodeValidator
     */
    private $validator;

    public function setUp()
    {
        $this->persister = $this->createMock('Scheb\TwoFactorBundle\Model\PersisterInterface');
        $this->validator = new BackupCodeValidator($this->persister);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createUser()
    {
        return $this->createMock('Scheb\TwoFactorBundle\Model\BackupCodeInterface');
    }

    /**
     * @test
     */
    public function checkBackupCode_userAndCodeGiven_validateCode()
    {
        //Mock the user
        $user = $this->createUser();
        $user
            ->expects($this->once())
            ->method('isBackupCode')
            ->with('c0de');

        $this->validator->checkCode($user, 'c0de');
    }

    /**
     * @test
     */
    public function checkBackupCode_codeInvalid_returnFalse()
    {
        //Stub the user
        $user = $this->createUser();
        $user
            ->expects($this->any())
            ->method('isBackupCode')
            ->willReturn(false);

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function checkBackupCode_codeValid_returnTrue()
    {
        //Stub the user
        $user = $this->createUser();
        $user
            ->expects($this->any())
            ->method('isBackupCode')
            ->willReturn(true);

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function checkBackupCode_codeValid_invalidateCode()
    {
        //Stub the user
        $user = $this->createUser();
        $user
            ->expects($this->any())
            ->method('isBackupCode')
            ->willReturn(true);

        //Mock code being invalidated
        $user
            ->expects($this->any())
            ->method('invalidateBackupCode')
            ->with('c0de');

        //Mock the persister
        $this->persister
            ->expects($this->any())
            ->method('persist')
            ->with($user);

        $returnValue = $this->validator->checkCode($user, 'c0de');
        $this->assertTrue($returnValue);
    }
}
