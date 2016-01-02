<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Session\SessionFlagManager;

class Voter implements VoterInterface
{
    /**
     * @var SessionFlagManager
     */
    protected $sessionFlagManager;

    /**
     * @var string[]
     */
    protected $providers;

    /**
     * @param SessionFlagManager $sessionFlagManager
     * @param string[]           $providers
     */
    public function __construct(SessionFlagManager $sessionFlagManager, array $providers)
    {
        $this->sessionFlagManager = $sessionFlagManager;
        $this->providers = $providers;
    }

    /**
     * @param string $class
     *
     * @return bool true
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * @param string $attribute
     *
     * @return bool true
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }

    /**
     * @param TokenInterface $token
     * @param mixed          $object
     * @param array          $attributes
     *
     * @return mixed result
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($this->providers as $providerName) {
            if ($this->sessionFlagManager->isNotAuthenticated($providerName, $token)) {
                return VoterInterface::ACCESS_DENIED;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
