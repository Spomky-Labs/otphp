<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderCollection;
use Scheb\TwoFactorBundle\Security\TwoFactor\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class VoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $provider;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderCollection
     **/
    protected $providerCollection;

    /**
        * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Voter
     **/
    protected $voter;

    public function setUp()
    {
        $this->provider = $this->getMock("Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface");

    }

    private function getProviderCollection($providers = true)
    {
        $providerCollection = new TwoFactorProviderCollection();
        if(true === $providers) {
            $providerCollection->addProvider('test', $this->provider);
        }

        return $providerCollection;
    }

    private function getSessionFlagManager()
    {
        $sessionFlagManager = $this->getMockBuilder("Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager")
            ->disableOriginalConstructor()
            ->getMock();

        return $sessionFlagManager;
    }

    private function getToken()
    {
        $token = $this->getMock("Symfony\Component\Security\Core\Authentication\Token\TokenInterface");

        return $token;
    }

    private function getVoter($providerCollection, $sessionFlagManager)
    {
        $voter = new Voter($sessionFlagManager, $providerCollection);

        return $voter;
    }

    /**
     * @test
     **/
    public function vote_notAuthenticated_withProviders()
    {
        $token = $this->getToken();

        $sessionFlagManager = $this->getSessionFlagManager();
        $providerCollection = $this->getProviderCollection();

        $sessionFlagManager
            ->expects($this->once())
            ->method("isNotAuthenticated")
            ->with('test', $token)
            ->will($this->returnValue(true));

        $voter = $this->getVoter($providerCollection, $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($token, null, array()));
    }

    /**
     * @test
     **/
    public function vote_notAuthenticated_noProviders()
    {
        $token = $this->getToken();

        $sessionFlagManager = $this->getSessionFlagManager();
        $providerCollection = $this->getProviderCollection(false);

        $sessionFlagManager
            ->expects($this->never())
            ->method("isNotAuthenticated");

        $voter = $this->getVoter($providerCollection, $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, null, array()));
    }

    /**
     * @test
     **/
    public function vote_authenticated_withProviders()
    {
        $token = $this->getToken();

        $sessionFlagManager = $this->getSessionFlagManager();
        $sessionFlagManager->setComplete('test', $token);

        $providerCollection = $this->getProviderCollection();

        $sessionFlagManager
            ->expects($this->once())
            ->method("isNotAuthenticated")
            ->with('test', $token)
            ->will($this->returnValue(false));

        $voter = $this->getVoter($providerCollection, $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, null, array()));
    }

    /**
     * @test
     **/
    public function vote_authenticated_noProviders()
    {
        $token = $this->getToken();

        $sessionFlagManager = $this->getSessionFlagManager();
        $sessionFlagManager->setComplete('test', $token);

        $providerCollection = $this->getProviderCollection(false);

        $sessionFlagManager
            ->expects($this->never())
            ->method("isNotAuthenticated");

        $voter = $this->getVoter($providerCollection, $sessionFlagManager);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, null, array()));
    }

    /**
     * @test
     **/
    public function voter_supportsClass()
    {
        $token = $this->getToken();

        $sessionFlagManager = $this->getSessionFlagManager();
        $providerCollection = $this->getProviderCollection();

        $voter = $this->getVoter($providerCollection, $sessionFlagManager);

        $returnValue = $voter->supportsClass('test');

        $this->assertTrue($returnValue);
    }

    /**
     * @test
     **/
    public function voter_supportsAttribute()
    {
        $token = $this->getToken();

        $sessionFlagManager = $this->getSessionFlagManager();
        $providerCollection = $this->getProviderCollection();

        $voter = $this->getVoter($providerCollection, $sessionFlagManager);

        $returnValue = $voter->supportsAttribute('test');

        $this->assertTrue($returnValue);
    }
}
