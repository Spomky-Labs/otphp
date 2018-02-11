<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManager;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerTokenStorage;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class TrustedComputerManagerTest extends TestCase
{
    /**
     * @var MockObject|TrustedComputerTokenStorage
     */
    private $trustedTokenStorage;

    /**
     * @var TrustedComputerManager
     */
    private $trustedComputerManager;

    protected function setUp()
    {
        $this->trustedTokenStorage = $this->createMock(TrustedComputerTokenStorage::class);
        $this->trustedComputerManager = new TrustedComputerManager($this->trustedTokenStorage);
    }

    private function stubUsername(MockObject $userMock, string $username)
    {
        $userMock
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn($username);
    }

    private function stubTrustedTokenVersion(MockObject $user, int $version)
    {
        $user
            ->expects($this->any())
            ->method('getTrustedTokenVersion')
            ->willReturn($version);
    }

    /**
     * @test
     */
    public function addTrustedComputer_notUserInterface_doNothing()
    {
        $this->trustedTokenStorage
            ->expects($this->never())
            ->method($this->anything());

        $user = new \stdClass();
        $this->trustedComputerManager->addTrustedComputer($user, 'firewallName');
    }

    /**
     * @test
     */
    public function addTrustedComputer_supportsTrustedComputerInterface_addTrustedTokenWithVersion()
    {
        $user = $this->createMock(UserInterfaceWithTrustedComputerInterface::class);
        $this->stubUsername($user, 'username');
        $this->stubTrustedTokenVersion($user, 123);

        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('addTrustedToken')
            ->with('username', 'firewallName', 123);

        $this->trustedComputerManager->addTrustedComputer($user, 'firewallName');
    }

    /**
     * @test
     */
    public function addTrustedComputer_notSupportsTrustedComputerInterface_addTrustedTokenWithDefaultVersion()
    {
        $user = $this->createMock(UserInterface::class);
        $this->stubUsername($user, 'username');

        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('addTrustedToken')
            ->with('username', 'firewallName', 0);

        $this->trustedComputerManager->addTrustedComputer($user, 'firewallName');
    }

    /**
     * @test
     */
    public function isTrustedComputer_notUserInterface_doNothing()
    {
        $this->trustedTokenStorage
            ->expects($this->never())
            ->method($this->anything());

        $user = new \stdClass();
        $this->trustedComputerManager->isTrustedComputer($user, 'firewallName');
    }

    /**
     * @test
     */
    public function isTrustedComputer_supportsTrustedComputerInterface_checkHasTrustedTokenWithVersion()
    {
        $user = $this->createMock(UserInterfaceWithTrustedComputerInterface::class);
        $this->stubUsername($user, 'username');
        $this->stubTrustedTokenVersion($user, 123);

        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('hasTrustedToken')
            ->with('username', 'firewallName', 123);

        $this->trustedComputerManager->isTrustedComputer($user, 'firewallName');
    }

    /**
     * @test
     */
    public function addTrustedComputer_notSupportsTrustedComputerInterface_checkHasTrustedTokenWithDefaultVersion()
    {
        $user = $this->createMock(UserInterface::class);
        $this->stubUsername($user, 'username');

        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('hasTrustedToken')
            ->with('username', 'firewallName', 0);

        $this->trustedComputerManager->isTrustedComputer($user, 'firewallName');
    }

    /**
     * @test
     * @dataProvider provideIsTrustedComputerReturnValues
     */
    public function addTrustedComputer_notSupportsTrustedComputerInterface_returnResult(bool $result)
    {
        $user = $this->createMock(UserInterface::class);
        $this->stubUsername($user, 'username');

        $this->trustedTokenStorage
            ->expects($this->once())
            ->method('hasTrustedToken')
            ->willReturn($result);

        $returnValue = $this->trustedComputerManager->isTrustedComputer($user, 'firewallName');
        $this->assertEquals($result, $returnValue);
    }


    public function provideIsTrustedComputerReturnValues(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
