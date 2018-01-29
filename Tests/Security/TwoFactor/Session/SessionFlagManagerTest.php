<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Session;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagGenerator;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SessionFlagManagerTest extends TestCase
{
    /**
     * @var MockObject|SessionInterface
     */
    private $session;

    /**
     * @var MockObject|SessionFlagGenerator
     */
    private $flagGenerator;

    /**
     * @var SessionFlagManager
     */
    private $sessionFlagManager;

    public function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->flagGenerator = $this->createMock(SessionFlagGenerator::class);
        $this->sessionFlagManager = new SessionFlagManager($this->session, $this->flagGenerator);
    }

    /**
     * @test
     */
    public function setBegin_startTwoFactor_flagIsSetFalse()
    {
        $token = $this->createMock(TokenInterface::class);

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->willReturn('session_flag');

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('session_flag', false);

        $this->sessionFlagManager->setBegin('providerName', $token);
    }

    /**
     * @test
     */
    public function setComplete_completeTwoFactor_flagIsSetTrue()
    {
        $token = $this->createMock(TokenInterface::class);

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->willReturn('session_flag');

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('set')
            ->with('session_flag', true);

        $this->sessionFlagManager->setComplete('providerName', $token);
    }

    /**
     * @test
     */
    public function setAborted_abortTwoFactor_flagIsRemoved()
    {
        $token = $this->createMock(TokenInterface::class);

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->willReturn('session_flag');

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('remove')
            ->with('session_flag');

        $this->sessionFlagManager->setAborted('providerName', $token);
    }

    /**
     * @test
     */
    public function isNotAuthenticated_notSessionStarted_returnFalse()
    {
        $token = $this->createMock(TokenInterface::class);

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->willReturn('session_flag');

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn(false);
        $this->session
            ->expects($this->never())
            ->method('has');

        $returnValue = $this->sessionFlagManager->isNotAuthenticated('providerName', $token);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     */
    public function isNotAuthenticated_notHasFlag_returnFalse()
    {
        $token = $this->createMock(TokenInterface::class);

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->willReturn('session_flag');

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn($this->returnValue(true));
        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('session_flag')
            ->willReturn(false);

        $returnValue = $this->sessionFlagManager->isNotAuthenticated('providerName', $token);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider dataProvider_isNotAuthenticated
     */
    public function isNotAuthenticated_hasFlagSet_returnCorrectBoolean($getReturnValue, $expectedReturnValue)
    {
        $token = $this->createMock(TokenInterface::class);

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->willReturn('session_flag');

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('isStarted')
            ->willReturn($this->returnValue(true));
        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('session_flag')
            ->willReturn(true);
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('session_flag')
            ->willReturn($getReturnValue);

        $returnValue = $this->sessionFlagManager->isNotAuthenticated('providerName', $token);
        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    public function dataProvider_isNotAuthenticated()
    {
        return [
            [true, false],
            [false, true],
        ];
    }
}
