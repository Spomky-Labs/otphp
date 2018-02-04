<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManager;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class TrustedComputerManagerTest extends TestCase
{
    /**
     * @var MockObject|PersisterInterface
     */
    private $persister;

    /**
     * @var TrustedComputerManager
     */
    private $trustedComputerManager;

    protected function setUp()
    {
        $this->persister = $this->createMock(PersisterInterface::class);
        $this->trustedComputerManager = new TrustedComputerManager($this->persister);
    }

    /**
     * @test
     */
    public function isTrustedComputer_notSupportsTrustedComputerInterface_returnFalse()
    {
        $user = $this->createMock(UserInterface::class);
        $returnValue = $this->trustedComputerManager->isTrustedComputer($user, 'trustedToken');
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider getIsTrustedComputerReturnValues
     */
    public function isTrustedComputer_supportsTrustedComputerInterface_returnResult($userReturnValue)
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $user
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->with('trustedToken')
            ->willReturn($userReturnValue);

        $returnValue = $this->trustedComputerManager->isTrustedComputer($user, 'trustedToken');
        $this->assertEquals($userReturnValue, $returnValue);
    }

    public function getIsTrustedComputerReturnValues()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @test
     */
    public function addTrustedComputer_notSupportsTrustedComputerInterface_notInvoked()
    {
        $user = $this->createMock(UserInterface::class);
        $user
            ->expects($this->never())
            ->method($this->anything());
        $this->trustedComputerManager->addTrustedComputer($user, 'trustedToken', new \DateTime('2014-01-01'));
    }

    /**
     * @test
     */
    public function addTrustedComputer_supportsTrustedComputerInterface_addTrustedComputerToken()
    {
        $user = $this->createMock(TrustedComputerInterface::class);
        $user
            ->expects($this->once())
            ->method('addTrustedComputer')
            ->with('trustedToken', new \DateTime('2014-01-01'));

        $this->persister
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->trustedComputerManager->addTrustedComputer($user, 'trustedToken', new \DateTime('2014-01-01'));
    }
}
