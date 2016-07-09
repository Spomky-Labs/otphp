<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\Session;

use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;

class SessionFlagManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $flagGenerator;

    /**
     * @var SessionFlagManager
     */
    private $sessionFlagManager;

    public function setUp()
    {
        $this->session = $this->createMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $this->flagGenerator = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagGenerator');
        $this->sessionFlagManager = new SessionFlagManager($this->session, $this->flagGenerator);
    }

    /**
     * @test
     */
    public function setBegin_startTwoFactor_flagIsSetFalse()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->will($this->returnValue('session_flag'));

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
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->will($this->returnValue('session_flag'));

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
    public function isNotAuthenticated_notHasFlag_returnFalse()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->will($this->returnValue('session_flag'));

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('session_flag')
            ->will($this->returnValue(false));

        $returnValue = $this->sessionFlagManager->isNotAuthenticated('providerName', $token);
        $this->assertFalse($returnValue);
    }

    /**
     * @test
     * @dataProvider dataProvider_isNotAuthenticated
     */
    public function isNotAuthenticated_hasFlagSet_returnCorrectBoolean($getReturnValue, $expectedReturnValue)
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        //Mock the SessionFlagGenerator
        $this->flagGenerator
            ->expects($this->once())
            ->method('getSessionFlag')
            ->with('providerName', $token)
            ->will($this->returnValue('session_flag'));

        //Mock the Session
        $this->session
            ->expects($this->once())
            ->method('has')
            ->with('session_flag')
            ->will($this->returnValue(true));
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with('session_flag')
            ->will($this->returnValue($getReturnValue));

        $returnValue = $this->sessionFlagManager->isNotAuthenticated('providerName', $token);
        $this->assertEquals($expectedReturnValue, $returnValue);
    }

    public function dataProvider_isNotAuthenticated()
    {
        return array(
            array(true, false),
            array(false, true),
        );
    }
}
