<?php

namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class VoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var Voter
     */
    protected $voter;

    public function setUp()
    {
        $this->provider = $this->createMock('Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface');
    }

    private function getSessionFlagManager()
    {
        $sessionFlagManager = $this->getMockBuilder('Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager')
            ->disableOriginalConstructor()
            ->getMock();

        return $sessionFlagManager;
    }

    private function getToken()
    {
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        return $token;
    }

    private function getVoter($providerCollection, $sessionFlagManager)
    {
        $voter = new Voter($sessionFlagManager, $providerCollection);

        return $voter;
    }

    /**
     * @test
     */
    public function vote_notAuthenticatedWithProviders_returnAccessDenied()
    {
        $token = $this->getToken();
        $sessionFlagManager = $this->getSessionFlagManager();

        $sessionFlagManager
            ->expects($this->once())
            ->method('isNotAuthenticated')
            ->with('test', $token)
            ->will($this->returnValue(true));

        $voter = $this->getVoter(array('test'), $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($token, null, array()));
    }

    /**
     * @test
     */
    public function vote_notAuthenticatedNoProviders_returnAccessDenied()
    {
        $token = $this->getToken();

        $sessionFlagManager = $this->getSessionFlagManager();
        $sessionFlagManager
            ->expects($this->never())
            ->method('isNotAuthenticated');

        $voter = $this->getVoter(array(), $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, null, array()));
    }

    /**
     * @test
     */
    public function vote_authenticatedWithProviders_returnAccessAbstain()
    {
        $token = $this->getToken();
        $sessionFlagManager = $this->getSessionFlagManager();

        $sessionFlagManager
            ->expects($this->once())
            ->method('isNotAuthenticated')
            ->with('test', $token)
            ->will($this->returnValue(false));

        $voter = $this->getVoter(array('test'), $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, null, array()));
    }

    /**
     * @test
     */
    public function vote_authenticatedNoProviders_returnAccessAbstain()
    {
        $token = $this->getToken();
        $sessionFlagManager = $this->getSessionFlagManager();

        $sessionFlagManager
            ->expects($this->never())
            ->method('isNotAuthenticated');

        $voter = $this->getVoter(array(), $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, null, array()));
    }

    /**
     * @test
     */
    public function voter_supportsClass_returnTrue()
    {
        $sessionFlagManager = $this->getSessionFlagManager();
        $voter = $this->getVoter(array('test'), $sessionFlagManager);

        $returnValue = $voter->supportsClass('test');

        $this->assertTrue($returnValue);
    }

    /**
     * @test
     */
    public function voter_supportsAttribute_returnFalse()
    {
        $sessionFlagManager = $this->getSessionFlagManager();
        $voter = $this->getVoter(array('test'), $sessionFlagManager);

        $returnValue = $voter->supportsAttribute('test');

        $this->assertTrue($returnValue);
    }
}
