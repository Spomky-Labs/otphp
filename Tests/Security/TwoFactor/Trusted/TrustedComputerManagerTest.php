<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Trusted;

use Scheb\TwoFactorBundle\Security\TwoFactor\Trusted\TrustedComputerManager;
use Symfony\Component\HttpFoundation\Cookie;

class TrustedComputerManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $persister;

    /**
     * @var TrustedComputerManager
     */
    private $trustedComputerManager;

    public function setUp()
    {
        $this->persister = $this->createMock('Scheb\TwoFactorBundle\Model\PersisterInterface');
        $this->trustedComputerManager = new TrustedComputerManager($this->persister);
    }

    /**
     * @test
     */
    public function isTrustedComputer_notSupportsTrustedComputerInterface_returnFalse()
    {
        $user = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
        $returnValue = $this->trustedComputerManager->isTrustedComputer($user, 'trustedToken');
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider getIsTrustedComputerReturnValues
     */
    public function isTrustedComputer_supportsTrustedComputerInterface_returnResult($userReturnValue)
    {
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\TrustedComputerInterface');
        $user
            ->expects($this->any())
            ->method('isTrustedComputer')
            ->with('trustedToken')
            ->will($this->returnValue($userReturnValue));

        $returnValue = $this->trustedComputerManager->isTrustedComputer($user, 'trustedToken');
        $this->assertEquals($userReturnValue, $returnValue);
    }

    public function getIsTrustedComputerReturnValues()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @test
     */
    public function addTrustedComputer_notSupportsTrustedComputerInterface_notInvoked()
    {
        $user = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
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
        $user = $this->createMock('Scheb\TwoFactorBundle\Model\TrustedComputerInterface');
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
