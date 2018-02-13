<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Backup;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManager;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class BackupCodeManagerTest extends TestCase
{
    /**
     * @var MockObject|PersisterInterface
     */
    private $persister;

    /**
     * @var BackupCodeManager
     */
    private $backupCodeManager;

    protected function setUp()
    {
        $this->persister = $this->createMock(PersisterInterface::class);
        $this->backupCodeManager = new BackupCodeManager($this->persister);
    }

    /**
     * @return MockObject|BackupCodeInterface
     */
    private function createUserWithBackupCodeInterface()
    {
        return $this->createMock(BackupCodeInterface::class);
    }

    /**
     * @test
     */
    public function isBackupCode_userNotImplementsInterface_returnFalse()
    {
        $user = new \stdClass();
        $returnValue = $this->backupCodeManager->isBackupCode($user, 'c0de');
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider provideCheckCodeResults
     */
    public function isBackupCode_userAndCodeGiven_returnValidationResultFromUser(bool $result)
    {
        $user = $this->createUserWithBackupCodeInterface();
        $user
            ->expects($this->any())
            ->method('isBackupCode')
            ->with('c0de')
            ->willReturn($result);

        $returnValue = $this->backupCodeManager->isBackupCode($user, 'c0de');
        $this->assertEquals($result, $returnValue);
    }

    public function provideCheckCodeResults(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @test
     */
    public function invalidateBackupCode_interfaceNotImplemented_doNothing()
    {
        $user = $this->createMock(UserInterface::class);
        $user
            ->expects($this->never())
            ->method($this->anything());

        $this->persister
            ->expects($this->never())
            ->method($this->anything());

        $this->backupCodeManager->invalidateBackupCode($user, 'c0de');
    }

    /**
     * @test
     */
    public function invalidateBackupCode_userAndCodeGiven_invalidateCodeOnUser()
    {
        $user = $this->createUserWithBackupCodeInterface();
        $user
            ->expects($this->once())
            ->method('invalidateBackupCode')
            ->with('c0de');

        $this->persister
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->backupCodeManager->invalidateBackupCode($user, 'c0de');
    }
}
