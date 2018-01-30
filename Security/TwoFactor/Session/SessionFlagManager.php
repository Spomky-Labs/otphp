<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SessionFlagManager
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var SessionFlagGenerator
     */
    private $flagGenerator;

    public function __construct(SessionInterface $session, SessionFlagGenerator $flagGenerator)
    {
        $this->session = $session;
        $this->flagGenerator = $flagGenerator;
    }

    public function setBegin(string $provider, TokenInterface $token): void
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);
        $this->session->set($sessionFlag, false);
    }

    public function setAborted(string $provider, TokenInterface $token): void
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);
        $this->session->remove($sessionFlag);
    }

    public function setComplete(string $provider, TokenInterface $token): void
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);
        $this->session->set($sessionFlag, true);
    }

    public function isNotAuthenticated(string $provider, TokenInterface $token): bool
    {
        $sessionFlag = $this->getSessionFlag($provider, $token);

        return $this->session->isStarted() && $this->session->has($sessionFlag) && !$this->session->get($sessionFlag);
    }

    private function getSessionFlag(string $provider, TokenInterface $token): string
    {
        return $this->flagGenerator->getSessionFlag($provider, $token);
    }
}
