<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderCollection;

/**
 * Class Voter
 */
class Voter implements VoterInterface
{
    /**
     * @var SessionFlagManager
     **/
    protected $sessionFlagManager;

    /**
     * @var array
     **/
    protected $providers;

    /**
     * __construct
     * @param SessionFlagManager          $sessionFlagManager
     * @param TwoFactorProviderCollection $providers
     * @return void
     **/
    public function __construct(SessionFlagManager $sessionFlagManager, TwoFactorProviderCollection $providerCollection)
    {
        $this->sessionFlagManager = $sessionFlagManager;
        $this->providers = $providerCollection->getProviders();
    }

    /**
     * supportsClass
     * @param string $class
     * @return boolean true
     **/
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * supportsAttribute
     * @param string $attribute
     * @return boolean true
     **/
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * vote
     * @param TokenInterface $token
     * @param mixed          $object
     * @param array          $attributes
     * @return mixed result
     **/
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($this->providers as $providerName => $provider) {
            $res = $this->sessionFlagManager->isNotAuthenticated($providerName, $token);
            if (true === $res) {
                return VoterInterface::ACCESS_DENIED;
            }
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }
}

